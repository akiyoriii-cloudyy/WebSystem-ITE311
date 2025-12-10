<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStudentFieldsToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'department_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'role',
                'comment'    => 'Foreign key to departments (for students)',
            ],
            'program_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'department_id',
                'comment'    => 'Foreign key to programs (for students)',
            ],
            'student_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'program_id',
                'comment'    => 'Student ID number',
            ],
        ];

        $this->forge->addColumn('users', $fields);

        // Add foreign keys if tables exist
        if ($this->db->tableExists('departments')) {
            $this->forge->addForeignKey('department_id', 'departments', 'id', 'CASCADE', 'CASCADE', 'users_department_fk');
        }
        if ($this->db->tableExists('programs')) {
            $this->forge->addForeignKey('program_id', 'programs', 'id', 'CASCADE', 'CASCADE', 'users_program_fk');
        }
    }

    public function down()
    {
        // Drop foreign keys first
        if ($this->db->tableExists('users')) {
            try {
                $this->db->query('ALTER TABLE users DROP FOREIGN KEY users_department_fk');
            } catch (\Exception $e) {}
            try {
                $this->db->query('ALTER TABLE users DROP FOREIGN KEY users_program_fk');
            } catch (\Exception $e) {}
        }

        $this->forge->dropColumn('users', ['department_id', 'program_id', 'student_id']);
    }
}
