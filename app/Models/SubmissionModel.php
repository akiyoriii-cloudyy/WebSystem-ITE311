<?php

namespace App\Models;

use CodeIgniter\Model;

class SubmissionModel extends Model
{
    protected $table            = 'submissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['quiz_id', 'user_id', 'answer', 'submitted_at', 'score', 'graded_by', 'graded_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = false; // Using submitted_at instead
    protected $dateFormat    = 'datetime';
    protected $createdField  = null;
    protected $updatedField  = null;
    protected $deletedField  = 'deleted_at';

    protected $validationRules      = [
        'quiz_id' => 'required|integer',
        'user_id' => 'required|integer',
        'answer'  => 'permit_empty',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setSubmissionDefaults'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function setSubmissionDefaults(array $data)
    {
        if (!isset($data['data']['submitted_at'])) {
            $data['data']['submitted_at'] = date('Y-m-d H:i:s');
        }
        return $data;
    }

    public function getSubmissionsByQuiz($quizId)
    {
        return $this->select('submissions.*, users.name as user_name, users.email, users.role, quizzes.title as quiz_title')
                    ->join('users', 'users.id = submissions.user_id', 'left')
                    ->join('quizzes', 'quizzes.id = submissions.quiz_id', 'left')
                    ->where('submissions.quiz_id', $quizId)
                    ->orderBy('submissions.submitted_at', 'DESC')
                    ->findAll();
    }

    public function getSubmissionsByUser($userId)
    {
        return $this->select('submissions.*, quizzes.title as quiz_title, quizzes.course_id, courses.title as course_title')
                    ->join('quizzes', 'quizzes.id = submissions.quiz_id', 'left')
                    ->join('courses', 'courses.id = quizzes.course_id', 'left')
                    ->where('submissions.user_id', $userId)
                    ->orderBy('submissions.submitted_at', 'DESC')
                    ->findAll();
    }

    public function getSubmissionByUserAndQuiz($userId, $quizId)
    {
        return $this->where('user_id', $userId)
                    ->where('quiz_id', $quizId)
                    ->first();
    }

    public function getSubmissionsByCourse($courseId)
    {
        return $this->select('submissions.*, users.name as user_name, users.email, quizzes.title as quiz_title')
                    ->join('users', 'users.id = submissions.user_id', 'left')
                    ->join('quizzes', 'quizzes.id = submissions.quiz_id', 'left')
                    ->where('quizzes.course_id', $courseId)
                    ->orderBy('submissions.submitted_at', 'DESC')
                    ->findAll();
    }
}

