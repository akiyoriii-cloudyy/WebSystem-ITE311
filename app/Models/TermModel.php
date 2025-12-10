<?php

namespace App\Models;

use CodeIgniter\Model;

class TermModel extends Model
{
    protected $table            = 'terms';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['semester_id', 'term', 'term_code', 'start_date', 'end_date', 'is_active'];

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
        'semester_id' => 'required|integer',
        'term'        => 'required|max_length[50]',
        'term_code'   => 'required|max_length[10]',
        'start_date'  => 'required|valid_date',
        'end_date'    => 'required|valid_date',
        'is_active'   => 'permit_empty|in_list[0,1]',
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

    public function getTermsBySemester($semesterId)
    {
        return $this->where('semester_id', $semesterId)
                    ->orderBy('start_date', 'ASC')
                    ->findAll();
    }

    public function getActiveTerm()
    {
        return $this->where('is_active', 1)->first();
    }
}
