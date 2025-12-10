<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAcadYearsTable extends Migration
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
            'acad_year' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'comment'    => 'Academic Year (e.g., 2024-2025)',
            ],
            'start_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'end_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '1 = Active, 0 = Inactive',
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
        $this->forge->addUniqueKey('acad_year');
        $this->forge->createTable('acad_years');
    }

    public function down()
    {
        $this->forge->dropTable('acad_years');
    }
}
