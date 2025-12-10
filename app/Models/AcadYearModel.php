<?php

namespace App\Models;

use CodeIgniter\Model;

class AcadYearModel extends Model
{
    protected $table            = 'acad_years';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['acad_year', 'start_date', 'end_date', 'is_active'];

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
        'acad_year'  => 'required|max_length[20]|is_unique[acad_years.acad_year,id,{id}]',
        'start_date' => 'required|valid_date',
        'end_date'   => 'required|valid_date',
        'is_active'  => 'permit_empty|in_list[0,1]',
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

    public function getActiveAcadYear()
    {
        return $this->where('is_active', 1)->first();
    }

    public function getAllAcadYears()
    {
        return $this->orderBy('acad_year', 'DESC')->findAll();
    }
}
