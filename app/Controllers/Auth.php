<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url'];

    // ✅ Login
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

            $user = $userModel->findUserByEmail($email);

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
                'user_role' => $user['role'],
                'logged_in' => true,
            ]);

            return redirect()->to('/dashboard')->with('success', 'You have successfully logged in.');
        }
    }

    // ✅ Register
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
                'role'     => $this->request->getPost('role'),
            ]);

            if (is_array($result)) {
                // ❌ Validation failed inside model
                return redirect()->back()->withInput()->with('errors', $result);
            }

            return redirect()->to('/auth/login')->with('success', 'Account created successfully. You can now login.');
        }
    }

    // ✅ Logout
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login')->with('success', 'You have been logged out.');
    }

    // 🔍 Helper: check if a table exists
    private function tableExists($tableName): bool
    {
        $db = \Config\Database::connect();
        return $db->query("SHOW TABLES LIKE " . $db->escape($tableName))->getNumRows() > 0;
    }

    // 🔍 Helper: check if column exists in table
    private function columnExists($tableName, $columnName): bool
    {
        $db = \Config\Database::connect();
        if (!$this->tableExists($tableName)) {
            return false;
        }
        $fields = $db->getFieldNames($tableName);
        return in_array($columnName, $fields);
    }

    // ✅ Dashboard
    public function dashboard()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/auth/login')->with('error', 'Please login first.');
        }

        $userRole = session()->get('user_role');
        $userId   = session()->get('user_id');

        $userModel = new UserModel();
        $stats     = $userModel->getDashboardStats($userRole, $userId);

        $db        = \Config\Database::connect();
        $users     = [];
        $courses   = [];
        $deadlines = [];

        try {
            // ✅ Admin
            if ($userRole === 'admin') {
                $users = $userModel->select('id, name, email, role')->findAll();

                if ($this->tableExists('courses')) {
                    $courses = $db->table('courses')->get()->getResultArray();
                }
            }
            // ✅ Teacher
            elseif ($userRole === 'teacher') {
                if ($this->tableExists('courses')) {
                    if ($this->columnExists('courses', 'teacher_id')) {
                        $courses = $db->table('courses')
                                      ->where('teacher_id', $userId)
                                      ->get()
                                      ->getResultArray();
                    } elseif ($this->columnExists('courses', 'created_by')) {
                        $courses = $db->table('courses')
                                      ->where('created_by', $userId)
                                      ->get()
                                      ->getResultArray();
                    } else {
                        $courses = $db->table('courses')->get()->getResultArray();
                    }
                }
            }
            // ✅ Student
            elseif ($userRole === 'student') {
                if ($this->tableExists('enrollments') && $this->tableExists('courses')) {
                    $query = $db->table('enrollments')
                                ->where('student_id', $userId)
                                ->join('courses', 'courses.id = enrollments.course_id')
                                ->select('courses.*')
                                ->get();
                    $courses = $query ? $query->getResultArray() : [];
                }

                if ($this->tableExists('assignments')) {
                    $deadlines = $db->table('assignments')
                                    ->where('student_id', $userId)
                                    ->where('due_date >=', date('Y-m-d'))
                                    ->orderBy('due_date', 'ASC')
                                    ->get()
                                    ->getResultArray();
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Dashboard query failed: ' . $e->getMessage());
            $courses   = [];
            $deadlines = [];
        }

        $data = [
            'title'      => ucfirst($userRole) . ' Dashboard',
            'user_name'  => session()->get('user_name'),
            'user_role'  => $userRole,
            'users'      => $users,
            'courses'    => $courses,
            'stats'      => $stats,
            'deadlines'  => $deadlines,
        ];

        return view('auth/dashboard', $data);
    }
}
