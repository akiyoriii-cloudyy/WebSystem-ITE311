<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddScoreFieldsToSubmissions extends Migration
{
    public function up()
    {
        // Check if score column doesn't exist
        if (!$this->db->fieldExists('score', 'submissions')) {
            $fields = [
                'score' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'null'       => true,
                    'after'      => 'answer',
                    'comment'    => 'Score given by teacher',
                ],
                'graded_by' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'score',
                    'comment'    => 'User ID of teacher who graded',
                ],
                'graded_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'graded_by',
                ],
            ];

            $this->forge->addColumn('submissions', $fields);

            // Add foreign key if users table exists
            if ($this->db->tableExists('users')) {
                $this->forge->addForeignKey('graded_by', 'users', 'id', 'CASCADE', 'CASCADE', 'submissions_graded_by_fk');
            }
        }
    }

    public function down()
    {
        // Drop foreign key first
        if ($this->db->tableExists('submissions')) {
            try {
                $this->db->query('ALTER TABLE submissions DROP FOREIGN KEY submissions_graded_by_fk');
            } catch (\Exception $e) {}
        }

        $this->forge->dropColumn('submissions', ['score', 'graded_by', 'graded_at']);
    }
}
