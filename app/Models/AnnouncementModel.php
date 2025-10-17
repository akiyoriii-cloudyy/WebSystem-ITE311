<?php

namespace App\Models;

use CodeIgniter\Model;

class AnnouncementModel extends Model
{
    protected $table      = 'announcements';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'content', 'created_by', 'created_at'];
    protected $useTimestamps = false;

    public function getAllAnnouncements()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }
}
