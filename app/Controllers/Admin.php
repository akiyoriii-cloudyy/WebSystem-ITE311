<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\AnnouncementModel;

class Admin extends BaseController
{
    protected $helpers = ['form', 'url'];

    // ✅ Admin Dashboard (redirects to unified dashboard)
    public function dashboard()
    {
        // Since Auth::dashboard handles all role-based dashboards,
        // redirect admin to the unified dashboard at /admin_dashboard
        return redirect()->to('/admin_dashboard');
    }

    // ✅ Manage Users
    public function users()
    {
        $session = session();

        // RoleAuth filter ensures only admin can access
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        $userModel = new UserModel();
        $users = $userModel->select('id, name, email, role, status, created_at')->findAll();

        $data = [
            'title'     => 'Manage Users',
            'users'     => $users,
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role')
        ];

        return view('admin/manage_users', $data);
    }

    // ✅ Manage Courses
    public function manageCourses()
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        $db = \Config\Database::connect();
        
        // Check if courses table exists
        $courses = [];
        if ($db->query("SHOW TABLES LIKE 'courses'")->getNumRows() > 0) {
            $courses = $db->table('courses')
                          ->select('courses.*, users.name as instructor_name')
                          ->join('users', 'courses.instructor_id = users.id', 'left')
                          ->orderBy('courses.created_at', 'DESC')
                          ->get()
                          ->getResultArray();
        }

        $data = [
            'title'     => 'Manage Courses',
            'courses'   => $courses,
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role')
        ];

        return view('admin/manage_courses', $data);
    }

    // ✅ Update User Role (AJAX)
    public function updateUserRole()
    {
        $session = session();

        // Ensure only admin can update roles
        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        if ($this->request->isAJAX()) {
            $userId = $this->request->getPost('id');
            $newRole = $this->request->getPost('role');

            // Validate
            if (!in_array($newRole, ['admin', 'teacher', 'student'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid role'
                ]);
            }

            $userModel = new UserModel();
            $userModel->update($userId, ['role' => strtolower($newRole)]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Role updated successfully!'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Invalid request'
        ]);
    }

    // ✅ Delete User
    public function deleteUser($userId)
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        // Prevent deleting yourself
        if ($userId == $session->get('user_id')) {
            return redirect()->to('/admin/users')->with('error', '⚠️ You cannot delete your own account!');
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/admin/users')->with('error', '⚠️ User not found.');
        }

        $userModel->delete($userId);

        return redirect()->to('/admin/users')->with('success', '✅ User deleted successfully!');
    }

    // ✅ Toggle User Status (active/inactive)
    public function toggleUserStatus($userId)
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/admin/users')->with('error', '⚠️ User not found.');
        }

        // Toggle status
        $newStatus = ($user['status'] ?? 'active') === 'active' ? 'inactive' : 'active';
        $userModel->update($userId, ['status' => $newStatus]);

        return redirect()->to('/admin/users')->with('success', "✅ User status updated to {$newStatus}!");
    }

    // ✅ View All Announcements
    public function announcements()
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        $announcementModel = new AnnouncementModel();
        $announcements = $announcementModel
            ->select('announcements.*, users.name as author_name')
            ->join('users', 'announcements.created_by = users.id', 'left')
            ->orderBy('announcements.created_at', 'DESC')
            ->findAll();

        $data = [
            'title'         => 'Manage Announcements',
            'announcements' => $announcements,
            'user_name'     => $session->get('user_name'),
            'user_role'     => $session->get('user_role')
        ];

        return view('admin/manage_announcements', $data);
    }
}