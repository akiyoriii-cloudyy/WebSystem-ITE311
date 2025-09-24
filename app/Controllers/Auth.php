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
            // Validate inputs
            if (!$this->validate([
                'email'    => 'required|valid_email',
                'password' => 'required|min_length[6]'
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $userModel = new UserModel();
            $email     = $this->request->getPost('email');
            $password  = $this->request->getPost('password');

            // ✅ Find user by email
            $user = $userModel->findUserByEmail($email);

            if (!$user) {
                return redirect()->back()->withInput()->with('error', 'Email not found.');
            }

            // ✅ Verify password hash
            if (!password_verify($password, $user['password'])) {
                return redirect()->back()->withInput()->with('error', 'Incorrect password.');
            }

            // ✅ Optional: check if account is active
            if (isset($user['status']) && $user['status'] !== 'active') {
                return redirect()->back()->with('error', 'Your account is not active. Please contact admin.');
            }

            // ✅ Store session data
            session()->set([
                'user_id'   => $user['id'],
                'user_name' => $user['name'],
                'user_role' => $user['role'],
                'logged_in' => true,
            ]);

            // 🔑 No duplicate welcome here, just short message
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
            $userModel->createAccount([
                'name'     => $this->request->getPost('name'),
                'email'    => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
                'role'     => $this->request->getPost('role'),
            ]);

            return redirect()->to('/auth/login')->with('success', 'Account created successfully. You can now login.');
        }
    }

    // ✅ Logout
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login')->with('success', 'You have been logged out.');
    }

    // ✅ Unified Dashboard (all roles in one view)
    public function dashboard()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/auth/login')->with('error', 'Please login first.');
        }

        $userRole = session()->get('user_role');
        $userId   = session()->get('user_id');

        $userModel = new UserModel();
        $stats = $userModel->getDashboardStats($userRole, $userId);

        // Common data
        $data = [
            'title'         => ucfirst($userRole) . ' Dashboard',
            'dashboard_url' => 'dashboard', // for sidebar links
            'user_name'     => session()->get('user_name'),
            'user_role'     => $userRole,
        ];

        // Merge role-specific stats
        $data = array_merge($data, $stats);

        // ✅ Single merged dashboard view
        return view('auth/dashboard', $data);
    }
}
