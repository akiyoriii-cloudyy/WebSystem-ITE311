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

    public function login()
    {
        // If already logged in, go straight to dashboard
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        $email    = trim($this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        $user = $this->users->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->with('error', 'Invalid email or password.')->withInput();
        }

        // Save minimal session
        session()->set([
            'user_id'   => $user['id'],
            'user_name' => $user['name'],
            'user_role' => $user['role'],
            'logged_in' => true,
        ]);

        return redirect()->to('/dashboard');
    }

    public function register()
    {
        return view('auth/register');
    }

    public function attemptRegister()
    {
        $data = $this->request->getPost([
            'name', 'email', 'password', 'role'
        ]);

        // Hash password before save
        $data['password'] = password_hash((string)$data['password'], PASSWORD_DEFAULT);

        if (!$this->users->save($data)) {
            return redirect()->back()->with('errors', $this->users->errors())->withInput();
        }

        return redirect()->to('/login')->with('success', 'Account created. Please log in.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
