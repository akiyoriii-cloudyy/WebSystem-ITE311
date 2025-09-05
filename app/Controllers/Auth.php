<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url'];
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    // Show login form
    public function login()
    {
        // Redirect already logged-in users
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    // Handle login form submission
    public function attemptLogin()
    {
        helper(['form']);

        $email    = trim($this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        // Basic validation
        if (empty($email) || empty($password)) {
            return redirect()->back()->with('error', 'Email and password are required.')->withInput();
        }

        $user = $this->users->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->with('error', 'Invalid email or password.')->withInput();
        }

        // Save session
        session()->set([
            'user_id'   => $user['id'],
            'user_name' => $user['name'],
            'user_role' => $user['role'],
            'logged_in' => true,
        ]);

        // Role-based redirect
        return redirect()->to('/dashboard'); // Dashboard controller will handle role-specific view
    }

    // Show registration form
    public function register()
    {
        return view('auth/register');
    }

    // Handle registration form submission
    public function attemptRegister()
    {
        helper(['form']);

        $rules = [
            'name'     => 'required|min_length[3]|max_length[255]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[admin,instructor,student]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'), // hashed automatically in UserModel
            'role'     => $this->request->getPost('role'),
        ];

        if (!$this->users->save($data)) {
            return redirect()->back()->with('errors', $this->users->errors())->withInput();
        }

        return redirect()->to('/login')->with('success', 'Account created successfully. You can now login.');
    }

    // Logout
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
