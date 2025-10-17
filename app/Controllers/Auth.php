<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AnnouncementModel;
use App\Models\EnrollmentModel;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url'];

    // ✅ LOGIN with Role-Based Redirection
    public function login()
    {
        $session = session();

        // Already logged in → Redirect by role
        if ($session->get('logged_in')) {
            return redirect()->to('/auth/dashboard');
        }

        if ($this->request->getMethod() === 'GET') {
            return view('auth/login');
        }

        if ($this->request->getMethod() === 'POST') {
            if (!$this->validate([
                'email'    => 'required|valid_email',
                'password' => 'required|min_length[6]'
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $userModel = new UserModel();
            $email     = $this->request->getPost('email');
            $password  = $this->request->getPost('password');
            $user      = $userModel->findUserByEmail($email);

            if (!$user) {
                return redirect()->back()->with('error', 'Email not found.');
            }

            if (!password_verify($password, $user['password'])) {
                return redirect()->back()->with('error', 'Incorrect password.');
            }

            if (isset($user['status']) && $user['status'] !== 'active') {
                return redirect()->back()->with('error', 'Your account is not active. Please contact admin.');
            }

            // ✅ Set session
            $session->set([
                'user_id'   => $user['id'],
                'user_name' => $user['name'],
                'user_role' => strtolower($user['role']),
                'logged_in' => true,
            ]);

            // ✅ Redirect to single dashboard
            return redirect()->to('/auth/dashboard');
        }
    }

    // ✅ REGISTER
    public function register()
    {
        if ($this->request->getMethod() === 'GET') {
            return view('auth/register');
        }

        if ($this->request->getMethod() === 'POST') {
            if (!$this->validate([
                'name'             => 'required|min_length[3]|max_length[255]',
                'email'            => 'required|valid_email|is_unique[users.email]',
                'password'         => 'required|min_length[6]',
                'confirm_password' => 'required|matches[password]',
                'role'             => 'required|in_list[admin,teacher,student]',
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $userModel = new UserModel();
            $userModel->createAccount([
                'name'     => $this->request->getPost('name'),
                'email'    => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
                'role'     => strtolower($this->request->getPost('role')),
            ]);

            return redirect()->to('/auth/login')->with('success', 'Account created successfully! You can now login.');
        }
    }

    // ✅ LOGOUT
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login')->with('success', 'You have been logged out.');
    }

    // ✅ SINGLE DASHBOARD for All Roles (with Announcements & Enrollment)
    public function dashboard()
    {
        $session = session();

        // 🔐 Check login
        if (!$session->get('logged_in')) {
            return redirect()->to('/auth/login')->with('error', 'Please login first.');
        }

        $db                = \Config\Database::connect();
        $userModel         = new UserModel();
        $announcementModel = new AnnouncementModel();
        $enrollmentModel   = new EnrollmentModel();

        $userId   = $session->get('user_id');
        $userRole = strtolower($session->get('user_role'));
        $user     = $userModel->find($userId);

        // ✅ Admin can create announcements directly from dashboard
        if ($this->request->getMethod() === 'POST' && $userRole === 'admin') {

            // --- Admin Role Update via AJAX ---
            if ($this->request->isAJAX() && $this->request->getPost('id') && $this->request->getPost('role')) {
                $userModel->update($this->request->getPost('id'), [
                    'role' => $this->request->getPost('role')
                ]);
                return $this->response->setJSON(['status' => 'success', 'message' => 'Role updated successfully!']);
            }

            // --- Admin Creating Announcement ---
            if ($this->request->getPost('title') && $this->request->getPost('content')) {
                $announcementModel->insert([
                    'title'      => $this->request->getPost('title'),
                    'content'    => $this->request->getPost('content'),
                    'created_by' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                return redirect()->to('/auth/dashboard')->with('success', '✅ Announcement created successfully!');
            }
        }

        // ✅ Student Enrollment Handling
        if ($this->request->getMethod() === 'POST' && $userRole === 'student') {
            $courseId = $this->request->getPost('course_id');

            // Prevent duplicate enrollment
            $exists = $enrollmentModel
                ->where('user_id', $userId)
                ->where('course_id', $courseId)
                ->first();

            if ($exists) {
                return redirect()->to('/auth/dashboard')->with('error', '⚠️ You are already enrolled in this course.');
            }

            $enrollmentModel->insert([
                'user_id'    => $userId,
                'course_id'  => $courseId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->to('/auth/dashboard')->with('success', '✅ Successfully enrolled in the course!');
        }

        // --- Admin Dashboard ---
        if ($userRole === 'admin') {
            $users = $userModel->select('id, name, email, role')->findAll();
            $courses = [];
            if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
                $courses = $db->table('courses')->get()->getResultArray();
            }
            $announcements = $announcementModel->orderBy('created_at', 'DESC')->findAll();

            $data = [
                'title'         => 'Admin Dashboard',
                'user'          => $user,
                'users'         => $users,
                'courses'       => $courses,
                'announcements' => $announcements,
                'user_name'     => $session->get('user_name'),
                'user_role'     => $userRole,
                'stats'         => [
                    'total_users'     => $userModel->countAll(),
                    'total_courses'   => $db->table('courses')->countAllResults(),
                    'active_students' => $userModel->where('role', 'student')->countAllResults(),
                    'active_teachers' => $userModel->where('role', 'teacher')->countAllResults(),
                ]
            ];

            return view('auth/dashboard', $data);
        }

        // --- Teacher Dashboard ---
        elseif ($userRole === 'teacher') {
            $courses = [];
            if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
                $courses = $db->table('courses')->where('instructor_id', $userId)->get()->getResultArray();
            }

            $announcements = $announcementModel->orderBy('created_at', 'DESC')->findAll();

            $data = [
                'title'         => 'Teacher Dashboard',
                'user'          => $user,
                'courses'       => $courses,
                'announcements' => $announcements,
                'user_name'     => $session->get('user_name'),
                'user_role'     => $userRole,
                'stats'         => [
                    'my_courses' => count($courses)
                ]
            ];

            return view('auth/dashboard', $data);
        }

        // --- Student Dashboard ---
        elseif ($userRole === 'student') {
            $courses = [];
            $enrolledCourses = [];

            if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
                $courses = $db->table('courses')->select('id, title, description')->get()->getResultArray();
            }

            if ($db->query("SHOW TABLES LIKE 'enrollments'")->getNumRows() > 0) {
                $enrolledCourses = $enrollmentModel
                    ->select('courses.id, courses.title, courses.description')
                    ->join('courses', 'enrollments.course_id = courses.id')
                    ->where('enrollments.user_id', $userId)
                    ->findAll();
            }

            $announcements = $announcementModel->orderBy('created_at', 'DESC')->findAll();

            $data = [
                'title'           => 'Student Dashboard',
                'user'            => $user,
                'courses'         => $courses,
                'enrolledCourses' => $enrolledCourses,
                'announcements'   => $announcements,
                'user_name'       => $session->get('user_name'),
                'user_role'       => $userRole,
                'stats'           => [
                    'my_courses' => count($enrolledCourses)
                ]
            ];

            return view('auth/dashboard', $data);
        }

        // --- Unknown Role ---
        else {
            return redirect()->to('/auth/login')->with('error', 'Unknown user role detected.');
        }
    }
}
