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

    // ✅ Make sure 'status' exists in your DB (active/inactive)
    protected $allowedFields = [
        'name', 'email', 'password', 'role', 'status'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // ✅ Validation rules
    protected $validationRules = [
        'name'     => 'required|min_length[3]|max_length[255]',
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[6]',
        'role'     => 'required|in_list[admin,teacher,student]',
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

    // 🔑 Find user by email
    public function findUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    // 🔑 Create account (hash password + default active status)
    public function createAccount($userData)
    {
        $data = [
            'name'     => $userData['name'],
            'email'    => $userData['email'],
            'password' => password_hash($userData['password'], PASSWORD_DEFAULT),
            'role'     => $userData['role'],
            'status'   => $userData['status'] ?? 'active', // 👈 default active
        ];

        if ($this->save($data)) {
            return $this->getInsertID();
        }

        // ❌ If insert fails, return validation errors
        return $this->errors();
    }

    // 🔑 Dashboard stats
    public function getDashboardStats($userRole, $userId = null)
    {
        $stats = [];
        $db = \Config\Database::connect();

        if ($userRole === 'admin') {
            $stats['total_users']     = $this->countAll();
            $stats['total_courses']   = $this->tableExists('courses')
                                        ? $db->table('courses')->countAll()
                                        : 0;
            $stats['active_students'] = $this->where(['role' => 'student', 'status' => 'active'])->countAllResults();
            $stats['active_teachers'] = $this->where(['role' => 'teacher', 'status' => 'active'])->countAllResults();
        } elseif ($userRole === 'teacher' && $userId) {
            $stats['my_courses'] = $this->tableExists('courses')
                                    ? $db->table('courses')
                                         ->where('teacher_id', $userId)
                                         ->countAllResults()
                                    : 0;
        } elseif ($userRole === 'student' && $userId) {
            $stats['my_courses'] = ($this->tableExists('enrollments') && $this->tableExists('courses'))
                                    ? $db->table('enrollments')
                                         ->where('student_id', $userId)
                                         ->join('courses', 'courses.id = enrollments.course_id')
                                         ->countAllResults()
                                    : 0;
        }

        return $stats;
    }

    // 🔍 Helper: check if table exists
    private function tableExists($tableName): bool
    {
        $db = \Config\Database::connect();
        return $db->query("SHOW TABLES LIKE " . $db->escape($tableName))->getNumRows() > 0;
    }

    // 🔑 Get all users
    public function getAllUsers()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }

    // 🔑 Verify credentials
    public function verifyCredentials($email, $password)
    {
        $user = $this->findUserByEmail($email);
        return ($user && password_verify($password, $user['password'])) ? $user : false;
    }
}
