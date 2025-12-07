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
        
        // Only show users that are NOT deleted (soft delete filter)
        // This excludes users with status = 'deleted' from the admin view
        // Users are preserved in the database but hidden from admin management
        // Query: Show users where status is NOT 'deleted' (includes NULL, empty, 'active', 'inactive')
        $db = \Config\Database::connect();
        $users = $db->table('users')
                    ->select('id, name, email, role, status, created_at')
                    ->where("(status != 'deleted' OR status IS NULL OR status = '')", null, false)
                    ->get()
                    ->getResultArray();

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

        if ($this->request->isAJAX() || $this->request->hasHeader('X-Requested-With')) {
            $userId = $this->request->getPost('id');
            $newRole = $this->request->getPost('role');

            // Validate input
            if (empty($userId) || empty($newRole)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Missing required parameters'
                ]);
            }

            // Validate role - prevent changing to or from admin
            if (!in_array($newRole, ['teacher', 'student'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid role. Admin role cannot be changed.'
                ]);
            }

            $userModel = new UserModel();
            $user = $userModel->find($userId);

            // Check if user exists
            if (!$user) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User not found'
                ]);
            }

            // Protect admin users - prevent changing admin role
            if (strtolower($user['role']) === 'admin') {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Admin role is protected and cannot be changed.'
                ]);
            }

            // Prevent changing to admin role
            if (strtolower($newRole) === 'admin') {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Cannot change user role to admin.'
                ]);
            }

            // Update the role
            try {
                $userModel->update($userId, ['role' => strtolower($newRole)]);
                
                // Get fresh CSRF token for next request
                $security = service('security');
                $newToken = $security->getHash();
                
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Role updated successfully!',
                    'new_role' => strtolower($newRole),
                    'csrf_token' => $newToken
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Failed to update role: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Invalid request'
        ]);
    }

    // ✅ Delete User (Soft Delete - Only hides from admin view, doesn't delete from database)
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

        // Prevent deleting admin users - Admin role is protected and cannot be deleted
        if (strtolower($user['role']) === 'admin') {
            return redirect()->to('/admin/users')->with('error', '⚠️ Admin users are protected and cannot be deleted. Only student and teacher users can be deleted.');
        }

        // Soft delete: Set status to 'deleted' instead of actually deleting from database
        // This hides the user from the admin manage users view but keeps them in the database
        // Only applies to student and teacher users
        $userModel->update($userId, ['status' => 'deleted']);

        return redirect()->to('/admin/users')->with('success', '✅ User removed from admin view successfully! (User data preserved in database)');
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

    // ✅ Settings Page (Password Change)
    public function settings()
    {
        $session = session();

        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        $data = [
            'title'     => 'Settings',
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role')
        ];

        return view('admin/settings', $data);
    }

    // ✅ User Management Page (Create Users - Admin Only)
    public function userManagement()
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return redirect()->to('/admin_dashboard')->with('error', 'Access Denied.');
        }

        $data = [
            'title'     => 'User Management',
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role')
        ];

        return view('admin/user_management', $data);
    }

    // ✅ Create User (Admin only)
    public function createUser()
    {
        $session = session();

        if (!$session->get('logged_in') || strtolower($session->get('user_role')) !== 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ])->setStatusCode(403);
        }

        if ($this->request->getMethod() === 'POST') {
            $userModel = new UserModel();

            $name = $this->request->getPost('name');
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            $role = $this->request->getPost('role');
            $status = $this->request->getPost('status') ?? 'active';

            // Validation
            if (empty($name) || empty($email) || empty($password) || empty($role)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'All fields are required'
                ]);
            }

            // Validate role
            if (!in_array(strtolower($role), ['admin', 'teacher', 'student'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid role'
                ]);
            }

            // Check if email already exists
            $existingUser = $userModel->findUserByEmail($email);
            if ($existingUser) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Email already exists'
                ]);
            }

            // Validate password length
            if (strlen($password) < 6) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Password must be at least 6 characters'
                ]);
            }

            // Create user
            try {
                // Don't hash password here - UserModel::createAccount() will hash it
                $userData = [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password, // Plain password - will be hashed in UserModel
                    'role' => strtolower($role),
                    'status' => $status
                ];

                $userId = $userModel->createAccount($userData);

                if ($userId) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'User created successfully!',
                        'csrf_token' => csrf_token(),
                        'csrf_hash' => csrf_hash()
                    ]);
                } else {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Failed to create user: ' . implode(', ', $userModel->errors())
                    ]);
                }
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error creating user: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Invalid request method'
        ]);
    }

    // ✅ Change Password (All users)
    public function changePassword()
    {
        $session = session();

        if (!$session->get('logged_in')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Please login first'
            ])->setStatusCode(401);
        }

        if ($this->request->getMethod() === 'POST') {
            $userModel = new UserModel();
            $userId = $session->get('user_id');

            $currentPassword = $this->request->getPost('current_password');
            $newPassword = $this->request->getPost('new_password');
            $confirmPassword = $this->request->getPost('confirm_password');

            // Validation
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'All fields are required'
                ]);
            }

            // Get current user
            $user = $userModel->find($userId);
            if (!$user) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User not found'
                ]);
            }

            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Current password is incorrect'
                ]);
            }

            // Check if new password matches confirmation
            if ($newPassword !== $confirmPassword) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'New password and confirmation do not match'
                ]);
            }

            // Validate password length
            if (strlen($newPassword) < 6) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Password must be at least 6 characters'
                ]);
            }

            // Check if new password is same as current
            if (password_verify($newPassword, $user['password'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'New password must be different from current password'
                ]);
            }

            // Update password
            try {
                $userModel->update($userId, [
                    'password' => password_hash($newPassword, PASSWORD_DEFAULT)
                ]);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Password changed successfully!',
                    'csrf_token' => csrf_token(),
                    'csrf_hash' => csrf_hash()
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error changing password: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Invalid request method'
        ]);
    }
}