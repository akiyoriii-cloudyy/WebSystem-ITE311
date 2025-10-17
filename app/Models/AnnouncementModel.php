<?php namespace App\Models;

use CodeIgniter\Model;

class AnnouncementModel extends Model
{
    protected $table      = 'announcements';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title','content','created_at'];
    // If you want automatic timestamps, configure createdField/updatedField instead.
    public function getAllOrderedDesc()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }
}
