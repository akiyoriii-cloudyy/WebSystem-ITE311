<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUnitsToCourses extends Migration
{
    public function up()
    {
        $fields = [
            'units' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'term_id',
                'comment'    => 'Number of units/credits for the course',
            ],
        ];

        $this->forge->addColumn('courses', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('courses', ['units']);
    }
}
