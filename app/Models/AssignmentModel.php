<?php

namespace App\Models;

use CodeIgniter\Model;

class AssignmentModel extends Model
{
    protected $table            = 'assignments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['course_id', 'grading_period_id', 'assignment_type', 'title', 'description', 'max_score', 'due_date'];

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
        'course_id'        => 'required|integer',
        'grading_period_id' => 'required|integer',
        'assignment_type'  => 'required|max_length[50]',
        'title'            => 'required|max_length[255]',
        'max_score'        => 'permit_empty|decimal',
        'due_date'         => 'permit_empty|valid_date',
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

    public function getAssignmentsByCourse($courseId)
    {
        return $this->where('course_id', $courseId)
                    ->orderBy('due_date', 'ASC')
                    ->findAll();
    }

    public function getAssignmentsByPeriod($periodId)
    {
        return $this->where('grading_period_id', $periodId)->findAll();
    }
}
