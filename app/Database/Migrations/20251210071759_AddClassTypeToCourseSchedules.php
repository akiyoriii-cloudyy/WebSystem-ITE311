<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClassTypeToCourseSchedules extends Migration
{
    public function up()
    {
        $fields = [
            'class_type' => [
                'type' => 'ENUM',
                'constraint' => ['online', 'face_to_face'],
                'default' => 'face_to_face',
                'null' => false,
                'after' => 'course_id',
                'comment' => 'Type of class: online or face_to_face',
            ],
            'meeting_link' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'after' => 'room',
                'comment' => 'Meeting link for online classes (Zoom, Google Meet, etc.)',
            ],
        ];

        $this->forge->addColumn('course_schedules', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('course_schedules', ['class_type', 'meeting_link']);
    }
}
