<?php

namespace App\Models;

use CodeIgniter\Model;

class QuizModel extends Model
{
    protected $table            = 'quizzes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['lesson_id', 'title', 'description', 'course_id', 'assignment_id', 'max_score', 'due_date'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules      = [
        'title'     => 'required|min_length[3]|max_length[255]',
        'course_id' => 'permit_empty|integer',
        'lesson_id' => 'permit_empty|integer',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getQuizzesByCourse($courseId)
    {
        return $this->select('quizzes.*, courses.title as course_title')
                    ->join('courses', 'courses.id = quizzes.course_id', 'left')
                    ->where('quizzes.course_id', $courseId)
                    ->orderBy('quizzes.created_at', 'DESC')
                    ->findAll();
    }

    public function getQuizzesByLesson($lessonId)
    {
        return $this->where('lesson_id', $lessonId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function getQuizWithSubmissions($quizId)
    {
        $quiz = $this->find($quizId);
        if (!$quiz) return null;

        $submissionModel = new SubmissionModel();
        $quiz['submissions'] = $submissionModel->getSubmissionsByQuiz($quizId);
        $quiz['submission_count'] = count($quiz['submissions']);

        return $quiz;
    }
}

