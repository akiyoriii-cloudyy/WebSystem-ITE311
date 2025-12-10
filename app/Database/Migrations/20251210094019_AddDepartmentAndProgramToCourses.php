<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDepartmentAndProgramToCourses extends Migration
{
    public function up()
    {
        $fields = [
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'term_id',
                'comment'    => 'Foreign key to departments',
            ],
            'program_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'department_id',
                'comment'    => 'Foreign key to programs',
            ],
        ];

        $this->forge->addColumn('courses', $fields);

        // Add foreign keys if tables exist
        try {
            if ($this->db->query("SHOW TABLES LIKE 'departments'")->getNumRows() > 0) {
                $this->forge->addForeignKey('department_id', 'departments', 'id', 'CASCADE', 'CASCADE', 'courses_department_fk');
            }
        } catch (\Exception $e) {
            // Table might not exist yet, skip foreign key
        }
        try {
            if ($this->db->query("SHOW TABLES LIKE 'programs'")->getNumRows() > 0) {
                $this->forge->addForeignKey('program_id', 'programs', 'id', 'CASCADE', 'CASCADE', 'courses_program_fk');
            }
        } catch (\Exception $e) {
            // Table might not exist yet, skip foreign key
        }
    }

    public function down()
    {
        // Drop foreign keys first
        try {
            $this->db->query('ALTER TABLE courses DROP FOREIGN KEY courses_department_fk');
        } catch (\Exception $e) {}
        try {
            $this->db->query('ALTER TABLE courses DROP FOREIGN KEY courses_program_fk');
        } catch (\Exception $e) {}

        $this->forge->dropColumn('courses', ['department_id', 'program_id']);
    }
}
