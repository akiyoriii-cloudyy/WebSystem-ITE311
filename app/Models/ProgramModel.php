<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramModel extends Model
{
    protected $table            = 'programs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['department_id', 'program_code', 'program_name', 'description', 'is_active'];

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
        'department_id' => 'required|integer',
        'program_code'  => 'required|max_length[20]',
        'program_name'  => 'required|max_length[255]',
        'is_active'     => 'permit_empty|in_list[0,1]',
    ];
    protected $validationMessages   = [
        'department_id' => [
            'required' => 'Department is required',
        ],
        'program_code' => [
            'required' => 'Program code is required',
        ],
        'program_name' => [
            'required' => 'Program name is required',
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
     * Get programs by department
     */
    public function getProgramsByDepartment($departmentId)
    {
        return $this->where('department_id', $departmentId)
                    ->where('is_active', 1)
                    ->orderBy('program_name', 'ASC')
                    ->findAll();
    }

    /**
     * Get active programs
     */
    public function getActivePrograms()
    {
        return $this->select('programs.*, departments.department_name, departments.department_code')
                    ->join('departments', 'departments.id = programs.department_id')
                    ->where('programs.is_active', 1)
                    ->orderBy('departments.department_name', 'ASC')
                    ->orderBy('programs.program_name', 'ASC')
                    ->findAll();
    }

    /**
     * Verify program belongs to department
     */
    public function verifyProgramDepartment($programId, $departmentId)
    {
        $program = $this->where('id', $programId)
                        ->where('department_id', $departmentId)
                        ->first();
        return $program !== null;
    }
}
