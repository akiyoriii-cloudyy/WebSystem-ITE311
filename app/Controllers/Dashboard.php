<?php

namespace App\Controllers;

use App\Models\UserModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please log in first.');
        }

        $role = session()->get('user_role');
        $userModel = new UserModel();

        $data = [
            'user_name' => session()->get('user_name'),
            'user_role' => $role,
            'total_users' => $role === 'admin' ? $userModel->countAll() : 0,
            'total_projects' => $role === 'admin' ? 5 : 0,
            'total_notifications' => $role === 'admin' ? 9 : 0,
            'my_courses' => $role === 'user' ? 3 : 0,  // Only user can have courses
            'my_notifications' => $role === 'user' ? 4 : 0,  // User-specific notifications
        ];

        // Load view from dashboard/index.php
        return view('dashboard/index', $data);
    }
}
