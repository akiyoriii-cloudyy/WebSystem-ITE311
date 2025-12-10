<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $table            = 'departments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['department_code', 'department_name', 'description', 'is_active'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'department_code' => 'required|max_length[20]|is_unique[departments.department_code,id,{id}]',
        'department_name' => 'required|max_length[255]',
        'is_active'       => 'permit_empty|in_list[0,1]',
    ];
    protected $validationMessages   = [
        'department_code' => [
            'required'   => 'Department code is required',
            'is_unique'  => 'Department code already exists',
        ],
        'department_name' => [
            'required' => 'Department name is required',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get active departments
     */
    public function getActiveDepartments()
    {
        return $this->where('is_active', 1)->orderBy('department_name', 'ASC')->findAll();
    }

    /**
     * Get department with programs
     */
    public function getDepartmentWithPrograms($departmentId)
    {
        $department = $this->find($departmentId);
        if ($department) {
            $programModel = new ProgramModel();
            $department['programs'] = $programModel->where('department_id', $departmentId)
                                                   ->where('is_active', 1)
                                                   ->findAll();
        }
        return $department;
    }
}
