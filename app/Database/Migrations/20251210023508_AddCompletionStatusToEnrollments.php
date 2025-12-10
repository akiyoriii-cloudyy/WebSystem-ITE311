<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompletionStatusToEnrollments extends Migration
{
    public function up()
    {
        $fields = [
            'completion_status' => [
                'type'       => 'ENUM',
                'constraint' => ['ENROLLED', 'IN_PROGRESS', 'COMPLETED', 'FAILED', 'DROPPED'],
                'default'    => 'ENROLLED',
                'after'      => 'enrollment_date',
                'comment'    => 'Student course completion status',
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'completion_status',
                'comment' => 'Date when course was completed',
            ],
            'final_grade' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'after'      => 'completed_at',
                'comment'    => 'Final grade for the course',
            ],
        ];

        $this->forge->addColumn('enrollments', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('enrollments', ['completion_status', 'completed_at', 'final_grade']);
    }
}
