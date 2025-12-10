<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeLessonIdNullableInQuizzes extends Migration
{
    public function up()
    {
        // Drop foreign key constraint first
        try {
            $this->db->query('ALTER TABLE quizzes DROP FOREIGN KEY quizzes_lesson_id_foreign');
        } catch (\Exception $e) {
            // Foreign key might not exist or have different name, try alternative
            try {
                $this->db->query('ALTER TABLE quizzes DROP FOREIGN KEY quizzes_ibfk_1');
            } catch (\Exception $e2) {
                // Ignore if foreign key doesn't exist
                log_message('debug', 'Foreign key drop attempted but may not exist: ' . $e2->getMessage());
            }
        }

        // Modify lesson_id to be nullable
        $fields = [
            'lesson_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
        ];

        $this->forge->modifyColumn('quizzes', $fields);
    }

    public function down()
    {
        // Make lesson_id NOT NULL again (but we can't easily restore the foreign key)
        $fields = [
            'lesson_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ];

        $this->forge->modifyColumn('quizzes', $fields);
    }
}
