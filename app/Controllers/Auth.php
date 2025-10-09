<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url'];

    // ✅ LOGIN
    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
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
                return redirect()->back()->withInput()->with('error', 'Email not found.');
            }

            if (!password_verify($password, $user['password'])) {
                return redirect()->back()->withInput()->with('error', 'Incorrect password.');
            }

            if (isset($user['status']) && $user['status'] !== 'active') {
                return redirect()->back()->with('error', 'Your account is not active. Please contact admin.');
            }

            session()->set([
                'user_id'   => $user['id'],
                'user_name' => $user['name'],
                'user_role' => strtolower($user['role']),
                'logged_in' => true,
            ]);

            return redirect()->to('/dashboard')->with('success', 'You have successfully logged in.');
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
                'name'              => 'required|min_length[3]|max_length[255]',
                'email'             => 'required|valid_email|is_unique[users.email]',
                'password'          => 'required|min_length[6]',
                'confirm_password'  => 'required|matches[password]',
                'role'              => 'required|in_list[admin,teacher,student]',
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $userModel = new UserModel();
            $result = $userModel->createAccount([
                'name'     => $this->request->getPost('name'),
                'email'    => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
                'role'     => strtolower($this->request->getPost('role')),
            ]);

            if (is_array($result)) {
                return redirect()->back()->withInput()->with('errors', $result);
            }

            return redirect()->to('/auth/login')->with('success', 'Account created successfully. You can now login.');
        }
    }

    // ✅ LOGOUT
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login')->with('success', 'You have been logged out.');
    }

    // ✅ DASHBOARD (Handles AJAX + Page Display)
    public function dashboard()
    {
        $session = session();

        if (!$session->get('logged_in')) {
            return redirect()->to('/auth/login')->with('error', 'Please login first.');
        }

        $db        = \Config\Database::connect();
        $userModel = new UserModel();

        $userId   = $session->get('user_id');
        $userRole = strtolower($session->get('user_role'));
        $user     = $userModel->find($userId);

        // ✅ Handle AJAX Role Update
        if ($this->request->getMethod() === 'post' && $this->request->isAJAX()) {
            $updateId = $this->request->getPost('id');
            $newRole  = strtolower($this->request->getPost('role'));

            if ($userRole !== 'admin') {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized access.']);
            }

            $validRoles = ['teacher', 'student'];
            if (!empty($updateId) && in_array($newRole, $validRoles)) {
                $updated = $userModel->update($updateId, ['role' => $newRole]);
                if ($updated) {
                    return $this->response->setJSON(['status' => 'success', 'message' => 'Role updated successfully.']);
                } else {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update role.']);
                }
            }

            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid input.']);
        }

        // ✅ Normal Dashboard View
        $users = [];
        $courses = [];
        $deadlines = [];
        $enrolledCourses = [];

        try {
            // ADMIN
            if ($userRole === 'admin') {
                $users = $userModel->select('id, name, email, role')->findAll();
                if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
                    $courses = $db->table('courses')->get()->getResultArray();
                }
            }

            // TEACHER
            elseif ($userRole === 'teacher') {
                if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
                    $courses = $db->table('courses')
                        ->where('instructor_id', $userId)
                        ->get()
                        ->getResultArray();
                }
            }

            // STUDENT
            elseif ($userRole === 'student') {
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

                    $enrolledIds = array_column($enrolledCourses, 'id');
                    $courses = array_filter($courses, fn($c) => !in_array($c['id'], $enrolledIds));
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Dashboard error: ' . $e->getMessage());
        }

        $stats = $userModel->getDashboardStats($userRole, $userId);

        $data = [
            'title'            => ucfirst($userRole) . ' Dashboard',
            'user'             => $user,
            'user_name'        => $session->get('user_name'),
            'user_role'        => $userRole,
            'users'            => $users,
            'courses'          => $courses,
            'enrolledCourses'  => $enrolledCourses,
            'stats'            => $stats,
            'deadlines'        => $deadlines,
        ];

        return view('auth/dashboard', $data);
    }
}
