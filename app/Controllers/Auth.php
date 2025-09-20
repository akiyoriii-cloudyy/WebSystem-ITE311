<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url'];

    // Handle login (GET: show form, POST: process login)
    public function login()
    {
        // Check if already logged in
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        // Handle GET request - show login form
        if ($this->request->getMethod() === 'GET') {
            return view('auth/login');
        }

        // Handle POST request - process login
        if ($this->request->getMethod() === 'POST') {
            if (!$this->validate([
                'email'    => 'required|valid_email',
                'password' => 'required|min_length[6]'
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $userModel = new UserModel();
            $user = $userModel->findUserByEmail($this->request->getPost('email'));

            if (!$user || !password_verify($this->request->getPost('password'), $user['password'])) {
                return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
            }

            session()->set([  
                'user_id'   => $user['id'],
                'user_name' => $user['name'],
                'user_role' => $user['role'],
                'logged_in' => true,
            ]);

            return redirect()->to('/dashboard');
        }
    }

    // Handle register (GET: show form, POST: process registration)
    public function register()
    {
        // Handle GET request - show register form
        if ($this->request->getMethod() === 'GET') {
            return view('auth/register');
        }

        // Handle POST request - process registration
        if ($this->request->getMethod() === 'POST') {
            if (!$this->validate([
                'name'              => 'required|min_length[3]|max_length[255]',
                'email'             => 'required|valid_email|is_unique[users.email]',
                'password'          => 'required|min_length[6]',
                'confirm_password'  => 'required|matches[password]',
                'role'              => 'required|in_list[admin,user]',
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

    // Logout
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login')->with('success', 'You have been logged out.');
    }

    // Dashboard
    public function dashboard()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/auth/login')->with('error', 'Please login first.');
        }

        $userRole = session()->get('user_role');
        $userId = session()->get('user_id');

        $data = [
            'user_name' => session()->get('user_name'),
            'user_role' => $userRole,
            'total_users' => 0,
            'total_projects' => 0,
            'total_notifications' => 0,
            'my_courses' => 0,
            'my_notifications' => 0,
        ];

        $userModel = new UserModel();
        $stats = $userModel->getDashboardStats($userRole, $userId);
        
        // Merge the stats into data array
        $data = array_merge($data, $stats);

        return view('dashboard/index', $data);
    }
}