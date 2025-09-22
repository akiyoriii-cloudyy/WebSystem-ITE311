<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'name', 'email', 'password', 'role', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation rules aligned with Auth controller
    protected $validationRules = [
        'name'     => 'required|min_length[3]|max_length[255]',
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[6]',
        'role'     => 'required|in_list[admin,teacher,student]', // ✅ updated roles
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
            'in_list'  => 'Role must be admin, teacher, or student'
        ]
    ];

    // Find user by email
    public function findUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    // Create new user
    public function createAccount($userData)
    {
        return $this->insert([
            'name'     => $userData['name'],
            'email'    => $userData['email'],
            'password' => password_hash($userData['password'], PASSWORD_DEFAULT),
            'role'     => $userData['role'],
        ]);
    }

    // Dashboard stats based on role
    public function getDashboardStats($userRole, $userId = null)
    {
        $stats = [
            'total_users'        => 0,
            'total_projects'     => 0,
            'total_notifications'=> 0,
            'my_courses'         => 0,
            'my_notifications'   => 0,
        ];

        $db = \Config\Database::connect();

        if ($userRole === 'admin') {
            $stats['total_users'] = $this->countAllResults();
            if ($db->tableExists('projects')) {
                $stats['total_projects'] = $db->table('projects')->countAllResults();
            }
            if ($db->tableExists('notifications')) {
                $stats['total_notifications'] = $db->table('notifications')->countAllResults();
            }

        } elseif (in_array($userRole, ['teacher','student']) && $userId) {
            if ($db->tableExists('courses')) {
                $stats['my_courses'] = $db->table('courses')->where('user_id', $userId)->countAllResults();
            }
            if ($db->tableExists('notifications')) {
                $stats['my_notifications'] = $db->table('notifications')->where('user_id', $userId)->countAllResults();
            }
        }

        return $stats;
    }

    // All users
    public function getAllUsers()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }

    // Users by role
    public function getUsersByRole($role)
    {
        return $this->where('role', $role)->findAll();
    }

    // Update role
    public function updateUserRole($userId, $newRole)
    {
        return $this->update($userId, ['role' => $newRole]);
    }

    // Check email existence
    public function emailExists($email, $excludeId = null)
    {
        $query = $this->where('email', $email);
        if ($excludeId) {
            $query->where('id !=', $excludeId);
        }
        return $query->first() !== null;
    }

    // Profile
    public function getUserProfile($userId)
    {
        return $this->select('id, name, email, role, created_at, updated_at')
                    ->where('id', $userId)
                    ->first();
    }

    public function updateProfile($userId, $data)
    {
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }
        return $this->update($userId, $data);
    }

    // Auto hash password
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            if (!password_get_info($data['data']['password'])['algo']) {
                $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
            }
        }
        return $data;
    }

    // Verify credentials
    public function verifyCredentials($email, $password)
    {
        $user = $this->findUserByEmail($email);
        return ($user && password_verify($password, $user['password'])) ? $user : false;
    }

    // Recent users
    public function getRecentUsers($limit = 5)
    {
        return $this->select('id, name, email, role, created_at')
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}
