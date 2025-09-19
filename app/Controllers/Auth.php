<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url'];
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // Show login form
    public function login()
    {
        if (session()->get('logged_in')) return redirect()->to('/dashboard');
        return view('auth/login');
    }

    // Handle login
    public function loginPost()
    {
        if (!$this->validate([
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user = $this->db->table('users')
            ->where('email', $this->request->getPost('email'))
            ->get()
            ->getRowArray();

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

    // Show register form
    public function register()
    {
        return view('auth/register');
    }

    // Handle register
    public function registerPost()
    {
        if (!$this->validate([
            'name'              => 'required|min_length[3]|max_length[255]',
            'email'             => 'required|valid_email|is_unique[users.email]',
            'password'          => 'required|min_length[6]',
            'confirm_password'  => 'required|matches[password]',
            'role'              => 'required|in_list[admin,user]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->db->table('users')->insert([
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'     => $this->request->getPost('role'),
        ]);

        return redirect()->to('/login')->with('success', 'Account created successfully. You can now login.');
    }

    // Logout
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'You have been logged out.');
    }

    // Dashboard
    public function dashboard()
    {
        if (!session()->get('logged_in')) return redirect()->to('/login')->with('error', 'Please login first.');

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

        $builder = $this->db;

        if ($userRole === 'admin') {
            if ($builder->tableExists('users')) $data['total_users'] = $builder->table('users')->countAllResults();
            if ($builder->tableExists('projects')) $data['total_projects'] = $builder->table('projects')->countAllResults();
            if ($builder->tableExists('notifications')) $data['total_notifications'] = $builder->table('notifications')->countAllResults();
        } elseif ($userRole === 'user') {
            if ($builder->tableExists('courses')) $data['my_courses'] = $builder->table('courses')->where('user_id', $userId)->countAllResults();
            if ($builder->tableExists('notifications')) $data['my_notifications'] = $builder->table('notifications')->where('user_id', $userId)->countAllResults();
        }

        return view('dashboard/index', $data);
    }
}
