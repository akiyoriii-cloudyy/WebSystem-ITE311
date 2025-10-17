<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'title'      => 'Welcome to the System!',
                'content'    => 'This is your first announcement. Stay tuned for updates.',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            
        ];

        $this->db->table('announcements')->insertBatch($data);
    }
}
