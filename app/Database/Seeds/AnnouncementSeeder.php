<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run()
    {
        // Define announcements
        $data = [
            [
                'title'      => 'Welcome to the Laboratory Management System',
                'content'    => 'All students and teachers are reminded to regularly check the dashboard for updates, announcements, and course-related information.',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title'      => 'Laboratory Safety Guidelines',
                'content'    => 'Please follow all laboratory safety protocols. Safety goggles and proper attire are mandatory for all lab sessions.',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title'      => 'Upcoming Laboratory Schedule',
                'content'    => 'The laboratory schedule for the next month has been published. Check your assigned courses and session timings to avoid conflicts.',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title'      => 'Laboratory Assessment Reminder',
                'content'    => 'All students are reminded that laboratory assessments and submissions are due according to the schedule. Late submissions will not be accepted.',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Check if the table exists before inserting
        if ($this->db->tableExists('announcements')) {
            $builder = $this->db->table('announcements');

            // Optional: clear previous entries to avoid duplicates
            // $builder->truncate();

            // Insert announcements
            $builder->insertBatch($data);
        } else {
            echo "The 'announcements' table does not exist. Please migrate your database first.";
        }
    }
}
