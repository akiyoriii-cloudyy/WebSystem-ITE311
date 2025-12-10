<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class LinkQuizzesToAssignments extends Migration
{
    public function up()
    {
        // Add assignment_id to quizzes table to link quizzes to assignments
        if (!$this->db->fieldExists('assignment_id', 'quizzes')) {
            $fields = [
                'assignment_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'course_id',
                    'comment'    => 'Foreign key to assignments (links quiz to assignment for grading)',
                ],
            ];

            $this->forge->addColumn('quizzes', $fields);

            // Add foreign key if assignments table exists
            if ($this->db->tableExists('assignments')) {
                $this->forge->addForeignKey('assignment_id', 'assignments', 'id', 'CASCADE', 'CASCADE', 'quizzes_assignment_fk');
            }
        }
    }

    public function down()
    {
        // Drop foreign key first
        if ($this->db->tableExists('quizzes')) {
            try {
                $this->db->query('ALTER TABLE quizzes DROP FOREIGN KEY quizzes_assignment_fk');
            } catch (\Exception $e) {}
        }

        $this->forge->dropColumn('quizzes', ['assignment_id']);
    }
}
