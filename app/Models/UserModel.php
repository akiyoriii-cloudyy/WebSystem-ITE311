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


    public function findUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }


    public function createAccount($userData)
    {
        $data = [
            'name'     => $userData['name'],
            'email'    => $userData['email'],
            'password' => password_hash($userData['password'], PASSWORD_DEFAULT),
            'role'     => $userData['role'],
            'status'   => $userData['status'] ?? 'active',
        ];

        if ($this->save($data)) {
            return $this->getInsertID();
        }

        return $this->errors();
    }


    public function getDashboardStats($userRole, $userId = null)
    {
        $db = \Config\Database::connect();
        $stats = [];

        try {
            $tableExists = fn($table) =>
                $db->query("SHOW TABLES LIKE '{$table}'")->getNumRows() > 0;

            if ($userRole === 'admin') {
                // ADMIN: overall system stats
                $stats['total_users']     = $this->countAll();
                $stats['active_students'] = $this->where(['role' => 'student', 'status' => 'active'])->countAllResults();
                $stats['active_teachers'] = $this->where(['role' => 'teacher', 'status' => 'active'])->countAllResults();
                $stats['total_courses']   = $tableExists('courses')
                    ? $db->table('courses')->countAllResults()
                    : 0;
            }

            elseif ($userRole === 'teacher' && $userId) {
                // TEACHER: show owned courses
                $stats['my_courses'] = $tableExists('courses')
                    ? $db->table('courses')
                        ->where('instructor_id', $userId)
                        ->countAllResults()
                    : 0;

                $stats['total_students'] = $tableExists('enrollments')
                    ? $db->table('enrollments')
                        ->join('courses', 'courses.id = enrollments.course_id')
                        ->where('courses.instructor_id', $userId)
                        ->countAllResults()
                    : 0;
            }

            elseif ($userRole === 'student' && $userId) {
                // STUDENT: enrolled courses
                $stats['my_courses'] = $tableExists('enrollments')
                    ? $db->table('enrollments')
                        ->where('user_id', $userId)
                        ->countAllResults()
                    : 0;

                $stats['total_available'] = $tableExists('courses')
                    ? $db->table('courses')->countAllResults()
                    : 0;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error in getDashboardStats: ' . $e->getMessage());
            $stats = [
                'total_users' => 0,
                'total_courses' => 0,
                'active_students' => 0,
                'active_teachers' => 0,
                'my_courses' => 0,
                'total_students' => 0,
                'total_available' => 0,
            ];
        }

        return $stats;
    }


    public function getAllUsers()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }

    public function verifyCredentials($email, $password)
    {
        $user = $this->findUserByEmail($email);
        return ($user && password_verify($password, $user['password'])) ? $user : false;
    }
}
