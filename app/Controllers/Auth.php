<?php

namespace App\Controllers;

use App\Models\UserModel;

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

            // ✅ Redirect to single dashboard (merged logic)
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

    // ✅ SINGLE DASHBOARD for All Roles
    public function dashboard()
{
    $session = session();

    // 🔐 Check login
    if (!$session->get('logged_in')) {
        return redirect()->to('/auth/login')->with('error', 'Please login first.');
    }

    $db        = \Config\Database::connect();
    $userModel = new UserModel();
    $userId    = $session->get('user_id');
    $userRole  = strtolower($session->get('user_role'));
    $user      = $userModel->find($userId);

    // --- Admin Section ---
    if ($userRole === 'admin') {
        $users = $userModel->select('id, name, email, role')->findAll();
        $courses = [];


        if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
            $courses = $db->table('courses')->get()->getResultArray();
        }

        $data = [
            'title'     => 'Admin Dashboard',
            'user'      => $user,
            'users'     => $users,
            'courses'   => $courses,
            'user_name' => $session->get('user_name'),
            'user_role' => $userRole
        ];

        // ✅ Use the unified dashboard view
        return view('auth/dashboard', $data);
    }

    // --- Teacher Section ---
    elseif ($userRole === 'teacher') {
        $courses = [];

        if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
            $courses = $db->table('courses')
                ->where('instructor_id', $userId)
                ->get()
                ->getResultArray();
        }

        $data = [
            'title'     => 'Teacher Dashboard',
            'user'      => $user,
            'courses'   => $courses,
            'user_name' => $session->get('user_name'),
            'user_role' => $userRole
        ];

        // ✅ Also use the unified dashboard view
        return view('auth/dashboard', $data);
    }

    // --- Student Section ---
    elseif ($userRole === 'student') {
        $courses = [];
        $enrolledCourses = [];

        if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
            $courses = $db->table('courses')
                ->select('id, title, description')
                ->get()
                ->getResultArray();
        }

        if ($db->query("SHOW TABLES LIKE 'enrollments'")->getNumRows() > 0) {
            $enrolledCourses = $db->table('enrollments')
                ->select('courses.id, courses.title, courses.description')
                ->join('courses', 'enrollments.course_id = courses.id')
                ->where('enrollments.user_id', $userId)
                ->get()
                ->getResultArray();
        }

        $announcementModel = new \App\Models\AnnouncementModel();
        $announcements = $announcementModel->orderBy('created_at', 'DESC')->findAll();

        $data = [
            'title'           => 'Student Announcements',
            'user'            => $user,
            'courses'         => $courses,
            'enrolledCourses' => $enrolledCourses,
            'announcements'   => $announcements,
            'user_name'       => $session->get('user_name'),
            'user_role'       => $userRole
        ];

        // ✅ Students use the announcements view
        return view('auth/announcements', $data);
    }

    // --- Unknown Role ---
    else {
        return redirect()->to('/auth/login')->with('error', 'Unknown user role detected.');
    }
}
}
