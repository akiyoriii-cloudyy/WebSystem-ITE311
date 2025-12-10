<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCourseSchedulesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'course_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Foreign key to courses',
            ],
            'day_of_week' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'comment'    => 'Day of week (e.g., Monday, Tuesday)',
            ],
            'start_time' => [
                'type' => 'TIME',
                'null' => false,
                'comment' => 'Class start time',
            ],
            'end_time' => [
                'type' => 'TIME',
                'null' => false,
                'comment' => 'Class end time',
            ],
            'room' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Room number or location',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('course_id', 'courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('course_schedules');
    }

    public function down()
    {
        $this->forge->dropTable('course_schedules');
    }
}
