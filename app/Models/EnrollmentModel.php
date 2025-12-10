<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{
    protected $table            = 'enrollments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'course_id', 'enrolled_at', 'enrollment_date', 'completion_status', 'completed_at', 'final_grade'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = false; // Disabled - using enrolled_at and enrollment_date instead
    protected $dateFormat    = 'datetime';
    protected $createdField  = null;
    protected $updatedField  = null;
    protected $deletedField  = 'deleted_at';

    protected $validationRules      = [
        'user_id'   => 'required|integer',
        'course_id' => 'required|integer',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setEnrollmentDefaults'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function setEnrollmentDefaults(array $data)
    {
        if (!isset($data['data']['completion_status'])) {
            $data['data']['completion_status'] = 'ENROLLED';
        }
        if (!isset($data['data']['enrolled_at']) && !isset($data['data']['enrollment_date'])) {
            $data['data']['enrolled_at'] = date('Y-m-d H:i:s');
            $data['data']['enrollment_date'] = date('Y-m-d H:i:s');
        }
        return $data;
    }

    public function enrollUser($data)
    {
        // Ensure required fields are set
        if (!isset($data['completion_status'])) {
            $data['completion_status'] = 'ENROLLED';
        }
        if (!isset($data['enrolled_at']) && !isset($data['enrollment_date'])) {
            $data['enrolled_at'] = date('Y-m-d H:i:s');
            $data['enrollment_date'] = date('Y-m-d H:i:s');
        }
        // Skip validation to avoid timestamp issues
        $this->skipValidation(true);
        $result = $this->insert($data);
        $this->skipValidation(false);
        return $result;
    }

    public function isAlreadyEnrolled($user_id, $course_id)
    {
        return $this->where('user_id', $user_id)
                    ->where('course_id', $course_id)
                    ->countAllResults() > 0;
    }

    public function getEnrollmentsByCourse($courseId)
    {
        return $this->select('enrollments.*, users.name, users.email, users.student_id')
                    ->join('users', 'users.id = enrollments.user_id')
                    ->where('enrollments.course_id', $courseId)
                    ->findAll();
    }

    public function getEnrollmentsByUser($userId)
    {
        return $this->select('enrollments.*, courses.title, courses.course_number, courses.description')
                    ->join('courses', 'courses.id = enrollments.course_id')
                    ->where('enrollments.user_id', $userId)
                    ->findAll();
    }
}
