<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'title'        => 'ITE 321 - Web Application Development',
                'description'  => 'Focuses on designing and building dynamic websites using modern frameworks.',
                'instructor_id'=> 2,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'title'        => 'ITE 322 - Database Systems and Analytics',
                'description'  => 'Explores data modeling, SQL queries, and database management principles.',
                'instructor_id'=> 2,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'title'        => 'ITE 323 - Software Design and Development',
                'description'  => 'Covers software lifecycle, project planning, and agile methodologies.',
                'instructor_id'=> 2,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('courses')->insertBatch($data);
    }
}
