<?php namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $data = [
            [
              'title' => 'Welcome to the Portal',
              'content' => 'Welcome students and faculty to the upgraded portal.',
              'created_at' => $now,
            ],
            [
              'title' => 'Scheduled Maintenance',
              'content' => 'System maintenance on Saturday 10:00 PM to 12:00 AM.',
              'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
        ];

        $this->db->table('announcements')->insertBatch($data);
    }
}
