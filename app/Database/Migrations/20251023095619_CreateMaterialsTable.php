<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMaterialsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'course_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'file_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'file_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        // Add simple FK if supported; otherwise skip silently
        try {
            $this->forge->addForeignKey('course_id', 'courses', 'id', 'CASCADE', 'CASCADE');
        } catch (\Throwable $e) {
            // Some DBs might not support FK here; it's okay for lab purposes
        }

        $this->forge->createTable('materials', true);
    }

    public function down()
    {
        $this->forge->dropTable('materials', true);
    }
}

