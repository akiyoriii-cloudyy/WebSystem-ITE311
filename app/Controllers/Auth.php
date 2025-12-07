<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AnnouncementModel;
use App\Models\EnrollmentModel;
use App\Models\MaterialModel;
use App\Models\NotificationModel;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url'];

    // ✅ LOGIN with Role-Based Redirection
    public function login()
    {
        $session = session();

        // ✅ If already logged in, redirect based on role
        if ($session->get('logged_in')) {
            $role = strtolower($session->get('user_role'));
            if ($role === 'admin') {
                return redirect()->to('/admin_dashboard');
            } elseif ($role === 'teacher') {
                return redirect()->to('/teacher_dashboard');
            } else {
                return redirect()->to('/dashboard');
            }
        }

        // ✅ Show login form if GET request
        if ($this->request->getMethod() === 'GET') {
            return view('auth/login');
        }

        // ✅ Process login POST
        if ($this->request->getMethod() === 'POST') {

            // Validation
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

            // ✅ Redirect based on role
            $role = strtolower($user['role']);
            if ($role === 'admin') {
                return redirect()->to('/admin_dashboard');
            } elseif ($role === 'teacher') {
                return redirect()->to('/teacher_dashboard');
            } else {
                return redirect()->to('/dashboard');
            }
        }
    }

    // ✅ REGISTER
    public function register()
    {
        $session = session();

        // If already logged in, redirect to dashboard
        if ($session->get('logged_in')) {
            $role = strtolower($session->get('user_role'));
            if ($role === 'admin') {
                return redirect()->to('/admin_dashboard');
            } elseif ($role === 'teacher') {
                return redirect()->to('/teacher_dashboard');
            } else {
                return redirect()->to('/dashboard');
            }
        }

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

            return redirect()->to('/login')->with('success', 'Account created successfully! You can now login.');
        }
    }

    // ✅ LOGOUT
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'You have been logged out.');
    }

    // ✅ SINGLE DASHBOARD for All Roles
    public function dashboard()
    {
        $session = session();

        // 🔐 Check login
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        $db                = \Config\Database::connect();
        $userModel         = new UserModel();
        $announcementModel = new AnnouncementModel();
        $enrollmentModel   = new EnrollmentModel();

        $userId   = $session->get('user_id');
        $user     = $userModel->find($userId);
        
        // Check if role changed in database and auto-redirect
        // Always use database role to ensure it's current
        $dbRole = '';
        if ($user) {
            $dbRole = strtolower($user['role'] ?? '');
            $sessionRole = strtolower($session->get('user_role') ?? '');
            
            if ($dbRole !== $sessionRole) {
                $session->set('user_role', $dbRole);
                
                if ($dbRole === 'admin') {
                    return redirect()->to('/admin_dashboard')->with('success', 'Your role has been updated to Admin.');
                } elseif ($dbRole === 'teacher') {
                    return redirect()->to('/teacher_dashboard')->with('success', 'Your role has been updated to Teacher.');
                } else {
                    return redirect()->to('/dashboard')->with('success', 'Your role has been updated to Student.');
                }
            }
        }
        
        // Always use database role (or session as fallback)
        $userRole = $dbRole ?: strtolower($session->get('user_role') ?? '');

        // ✅ Admin: create announcements & update roles
        if ($this->request->getMethod() === 'POST' && $userRole === 'admin') {

            // AJAX role update
            if ($this->request->isAJAX() && $this->request->getPost('id') && $this->request->getPost('role')) {
                $userModel->update($this->request->getPost('id'), [
                    'role' => $this->request->getPost('role')
                ]);
                return $this->response->setJSON(['status' => 'success', 'message' => 'Role updated successfully!']);
            }

            // Create announcement
            if ($this->request->getPost('title') && $this->request->getPost('content')) {
                $announcementModel->insert([
                    'title'      => $this->request->getPost('title'),
                    'content'    => $this->request->getPost('content'),
                    'created_by' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                return redirect()->to('/admin_dashboard')->with('success', '✅ Announcement created successfully!');
            }
        }

        // ✅ Student Enrollment
        if ($this->request->getMethod() === 'POST' && $userRole === 'student') {
            $courseId = $this->request->getPost('course_id');
            $exists = $enrollmentModel->where('user_id', $userId)->where('course_id', $courseId)->first();

            if ($exists) {
                return redirect()->to('/dashboard')->with('error', '⚠️ You are already enrolled in this course.');
            }

            $enrollmentModel->insert([
                'user_id'    => $userId,
                'course_id'  => $courseId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // ✅ Create notification for the student
            $notificationModel = new NotificationModel();
            $course = $db->table('courses')->where('id', $courseId)->get()->getRowArray();
            $courseName = $course ? $course['title'] : 'a course';
            
            $notificationModel->createNotification(
                $userId,
                "You have been successfully enrolled in {$courseName}"
            );

            return redirect()->to('/dashboard')->with('success', '✅ Successfully enrolled in the course!');
        }

        // --- Load dashboard views by role ---
        if ($userRole === 'admin') {
            $users = $userModel->select('id, name, email, role')->findAll();
            
            // Check if courses table exists
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
                    'total_courses'   => count($courses),
                    'active_students' => $userModel->where('role', 'student')->countAllResults(),
                    'active_teachers' => $userModel->where('role', 'teacher')->countAllResults(),
                ]
            ];

            // ✅ FIXED: Correct view path
            return view('/admin_dashboard', $data);
        }


        elseif ($userRole === 'teacher') {
            // Check if courses table exists
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
                'stats'         => ['my_courses' => count($courses)]
            ];

            // ✅ FIXED: Correct view path
            return view('/teacher_dashboard', $data);
        }


        elseif ($userRole === 'student') {
            // Check if courses table exists
            $courses = [];

            
            if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
                // Check if code column exists
                $hasCodeColumn = false;
                try {
                    $hasCodeColumn = $db->query("SHOW COLUMNS FROM courses WHERE Field = 'code'")->getNumRows() > 0;
                } catch (\Exception $e) {
                    // Column check failed, assume it doesn't exist
                    $hasCodeColumn = false;
                }
                
                // Build select string based on available columns
                $selectFields = 'courses.id, courses.title, courses.description';
                if ($hasCodeColumn) {
                    $selectFields .= ', courses.code';
                }
                $selectFields .= ', users.name as instructor_name';
                
                // Build query
                $query = $db->table('courses')
                    ->select($selectFields)
                    ->join('users', 'courses.instructor_id = users.id', 'left');
                
                $result = $query->get();
                
                // Check if query was successful
                if ($result !== false) {
                    $courses = $result->getResultArray();
                    // Ensure code exists in array even if column doesn't exist
                    foreach ($courses as &$course) {
                        if (!isset($course['code'])) {
                            $course['code'] = '';
                        }
                        if (!isset($course['instructor_name'])) {
                            $course['instructor_name'] = 'N/A';
                        }
                    }
                } else {
                    // If join fails, try without join
                    try {
                        $result = $db->table('courses')
                            ->select('courses.id, courses.title, courses.description')
                            ->get();
                        
                        if ($result !== false) {
                            $courses = $result->getResultArray();
                            foreach ($courses as &$course) {
                                $course['code'] = '';
                                $course['instructor_name'] = 'N/A';
                            }
                        }
                    } catch (\Exception $e) {
                        // Query failed completely, courses will remain empty array
                        $courses = [];
                    }
                }
            }
            
            // Check if enrollments table exists
            $enrolledCourses = [];
            if ($db->query("SHOW TABLES LIKE 'enrollments'")->getNumRows() > 0) {
                $enrolledCourses = $enrollmentModel->select('courses.id, courses.title, courses.description')
                    ->join('courses', 'enrollments.course_id = courses.id')
                    ->where('enrollments.user_id', $userId)
                    ->findAll();
            }
            
            // Load materials per enrolled course
            $materialsByCourse = [];
            if (!empty($enrolledCourses)) {
                $materialModel = new MaterialModel();
                foreach ($enrolledCourses as $ec) {
                    $materialsByCourse[$ec['id']] = $materialModel->getMaterialsByCourse($ec['id']);
                }
            }
            
            $announcements = $announcementModel->orderBy('created_at', 'DESC')->findAll();

            $data = [
                'title'           => 'Student Dashboard',
                'user'            => $user,
                'courses'         => $courses,
                'enrolledCourses' => $enrolledCourses,
                'materialsByCourse' => $materialsByCourse,
                'announcements'   => $announcements,
                'user_name'       => $session->get('user_name'),
                'user_role'       => $userRole,
                'stats'           => ['my_courses' => count($enrolledCourses)]
            ];

            return view('/dashboard', $data);
        }


        else {
            $session->destroy();
            return redirect()->to('/login')->with('error', 'Unknown user role detected.');
        }
    }
}
