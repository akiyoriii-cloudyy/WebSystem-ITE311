<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAcademicFieldsToCourses extends Migration
{
    public function up()
    {
        $fields = [
            'course_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'id',
                'comment'    => 'CN - Course Number/Code (e.g., CS101, IT311)',
            ],
            'acad_year_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'course_number',
                'comment'    => 'Foreign key to acad_years',
            ],
            'semester_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'acad_year_id',
                'comment'    => 'Foreign key to semesters',
            ],
            'term_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'semester_id',
                'comment'    => 'Foreign key to terms',
            ],
        ];

        $this->forge->addColumn('courses', $fields);

        // Add foreign keys if tables exist
        if ($this->db->tableExists('acad_years')) {
            $this->forge->addForeignKey('acad_year_id', 'acad_years', 'id', 'CASCADE', 'CASCADE', 'courses_acad_year_fk');
        }
        if ($this->db->tableExists('semesters')) {
            $this->forge->addForeignKey('semester_id', 'semesters', 'id', 'CASCADE', 'CASCADE', 'courses_semester_fk');
        }
        if ($this->db->tableExists('terms')) {
            $this->forge->addForeignKey('term_id', 'terms', 'id', 'CASCADE', 'CASCADE', 'courses_term_fk');
        }
    }

    public function down()
    {
        // Drop foreign keys first
        if ($this->db->tableExists('courses')) {
            try {
                $this->db->query('ALTER TABLE courses DROP FOREIGN KEY courses_acad_year_fk');
            } catch (\Exception $e) {}
            try {
                $this->db->query('ALTER TABLE courses DROP FOREIGN KEY courses_semester_fk');
            } catch (\Exception $e) {}
            try {
                $this->db->query('ALTER TABLE courses DROP FOREIGN KEY courses_term_fk');
            } catch (\Exception $e) {}
        }

        $this->forge->dropColumn('courses', ['course_number', 'acad_year_id', 'semester_id', 'term_id']);
    }
}
