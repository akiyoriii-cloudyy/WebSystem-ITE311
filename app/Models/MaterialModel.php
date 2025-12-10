<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialModel extends Model
{
    protected $table = 'materials';
    protected $primaryKey = 'id';
    protected $allowedFields = ['course_id', 'file_name', 'file_path', 'created_at', 'status'];
    public $useTimestamps = false;

    public function insertMaterial($data)
    {
        return $this->insert($data);
    }

    public function getMaterialsByCourse($course_id, $includeDeleted = false)
    {
        $query = $this->where('course_id', $course_id);
        
        if (!$includeDeleted) {
            // Include materials with status 'active' or NULL (for backward compatibility with existing records)
            $query->groupStart()
                  ->where('status', 'active')
                  ->orWhere('status IS NULL')
                  ->groupEnd();
        }
        
        return $query->orderBy('created_at', 'DESC')->findAll();
    }

    public function getDeletedMaterialsByCourse($course_id)
    {
        return $this->where('course_id', $course_id)
                    ->where('status', 'deleted')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function softDelete($material_id)
    {
        return $this->update($material_id, ['status' => 'deleted']);
    }

    public function restore($material_id)
    {
        return $this->update($material_id, ['status' => 'active']);
    }
}
