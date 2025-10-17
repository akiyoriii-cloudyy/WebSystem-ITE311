<?php

namespace App\Models;

use CodeIgniter\Model;

class AnnouncementModel extends Model
{
    protected $table      = 'announcements';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'content', 'created_by', 'created_at'];
    protected $useTimestamps = false;

    // ✅ Fetch all announcements (newest first)
    public function getAllAnnouncements()
    {
        return $this->orderBy('created_at', 'DESC')->findAll();
    }

    // ✅ Optional: Fetch announcements by specific admin/user
    public function getAnnouncementsByUser($userId)
    {
        return $this->where('created_by', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
