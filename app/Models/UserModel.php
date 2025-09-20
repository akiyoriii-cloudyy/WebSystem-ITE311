<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $useAutoIncrement = true;

    protected $returnType    = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name', 'email', 'password', 'role', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;          // auto-fill created_at / updated_at
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation rules aligned with Auth controller
    protected $validationRules = [
        'name'     => 'required|min_length[3]|max_length[255]',  // Match Auth controller validation
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[6]',
        'role'     => 'required|in_list[admin,user]',  // Match Auth controller roles
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Name is required',
            'min_length' => 'Name must be at least 3 characters long',
            'max_length' => 'Name cannot exceed 255 characters'
        ],
        'email' => [
            'required'    => 'Email is required',
            'valid_email' => 'Please provide a valid email address',
            'is_unique'   => 'This email is already registered'
        ],
        'password' => [
            'required'   => 'Password is required',
            'min_length' => 'Password must be at least 6 characters long'
        ],
        'role' => [
            'required' => 'Role is required',
            'in_list'  => 'Role must be either admin or user'
        ]
    ];

    // Method to find user by email for login authentication
    public function findUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    // Method to create new user account
    public function createAccount($userData)
    {
        return $this->insert([
            'name'     => $userData['name'],
            'email'    => $userData['email'],
            'password' => password_hash($userData['password'], PASSWORD_DEFAULT),
            'role'     => $userData['role'],
        ]);
    }

    // Method to get dashboard statistics based on user role
    public function getDashboardStats($userRole, $userId = null)
    {
        $stats = [
            'total_users' => 0,
            'total_projects' => 0,
            'total_notifications' => 0,
            'my_courses' => 0,
            'my_notifications' => 0,
        ];

        if ($userRole === 'admin') {
            // Admin statistics
            $stats['total_users'] = $this->countAllResults();
            
            // Check if other tables exist and get counts
            $db = \Config\Database::connect();
            if ($db->tableExists('projects')) {
                $stats['total_projects'] = $db->table('projects')->countAllResults();
            }
            if ($db->tableExists('notifications')) {
                $stats['total_notifications'] = $db->table('notifications')->countAllResults();
            }
            
        } elseif ($userRole === 'user' && $userId) {
            // User statistics
            $db = \Config\Database::connect();
            if ($db->tableExists('courses')) {
                $stats['my_courses'] = $db->table('courses')->where('user_id', $userId)->countAllResults();
            }
            if ($db->tableExists('notifications')) {
                $stats['my_notifications'] = $db->table('notifications')->where('user_id', $userId)->countAllResults();
            }
        }

        return $stats;
    }

    // Method to get all users for admin dashboard
    public function getAllUsers()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }

    // Method to get users by role
    public function getUsersByRole($role)
    {
        return $this->where('role', $role)->findAll();
    }

    // Method to update user role (admin functionality)
    public function updateUserRole($userId, $newRole)
    {
        return $this->update($userId, ['role' => $newRole]);
    }

    // Method to check if email exists (for registration validation)
    public function emailExists($email, $excludeId = null)
    {
        $query = $this->where('email', $email);
        if ($excludeId) {
            $query->where('id !=', $excludeId);
        }
        return $query->first() !== null;
    }

    // Method to get user profile data
    public function getUserProfile($userId)
    {
        return $this->select('id, name, email, role, created_at, updated_at')
                    ->where('id', $userId)
                    ->first();
    }

    // Method to update user profile
    public function updateProfile($userId, $data)
    {
        // Remove password from data if empty
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }
        
        return $this->update($userId, $data);
    }

    // Automatically hash password before insert/update
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            // Only hash if password is not already hashed
            if (!password_get_info($data['data']['password'])['algo']) {
                $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
            }
        }
        return $data;
    }

    // Method to verify user credentials (for login)
    public function verifyCredentials($email, $password)
    {
        $user = $this->findUserByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }

    // Method to get recent users (for admin dashboard)
    public function getRecentUsers($limit = 5)
    {
        return $this->select('id, name, email, role, created_at')
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}