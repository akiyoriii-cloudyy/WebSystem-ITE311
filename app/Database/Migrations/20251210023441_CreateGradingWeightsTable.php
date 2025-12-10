<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGradingWeightsTable extends Migration
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
            'term_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Foreign key to terms (for term-based weights)',
            ],
            'assignment_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'comment'    => 'Assignment type (e.g., Quiz, Exam, Project)',
            ],
            'weight_percentage' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'comment'    => 'Weight percentage for this assignment type (e.g., 30.00 for 30%)',
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
        $this->forge->addForeignKey('term_id', 'terms', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('grading_weights');
    }

    public function down()
    {
        $this->forge->dropTable('grading_weights');
    }
}
