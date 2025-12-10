<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCourseIdToQuizzes extends Migration
{
    public function up()
    {
        // Check if course_id column doesn't exist
        if (!$this->db->fieldExists('course_id', 'quizzes')) {
            $fields = [
                'course_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'lesson_id',
                    'comment'    => 'Foreign key to courses (alternative to lesson_id)',
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'title',
                ],
                'max_score' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 100.00,
                    'null'       => true,
                    'after'      => 'description',
                ],
                'due_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'max_score',
                ],
            ];

            $this->forge->addColumn('quizzes', $fields);

            // Add foreign key if courses table exists
            if ($this->db->tableExists('courses')) {
                $this->forge->addForeignKey('course_id', 'courses', 'id', 'CASCADE', 'CASCADE', 'quizzes_course_fk');
            }
        }
    }

    public function down()
    {
        // Drop foreign key first
        if ($this->db->tableExists('quizzes')) {
            try {
                $this->db->query('ALTER TABLE quizzes DROP FOREIGN KEY quizzes_course_fk');
            } catch (\Exception $e) {}
        }

        $this->forge->dropColumn('quizzes', ['course_id', 'description', 'max_score', 'due_date']);
    }
}
