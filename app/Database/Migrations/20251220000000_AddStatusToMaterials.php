<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToMaterials extends Migration
{
    public function up()
    {
        // Check if status column already exists
        try {
            $result = $this->db->query("SHOW COLUMNS FROM materials LIKE 'status'");
            $statusExists = $result->getNumRows() > 0;
        } catch (\Exception $e) {
            // If query fails, assume column doesn't exist
            $statusExists = false;
        }

        if (!$statusExists) {
            $fields = [
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'active',
                    'null'       => false,
                    'after'      => 'file_path',
                    'comment'    => 'Material status: active or deleted',
                ],
            ];

            $this->forge->addColumn('materials', $fields);
            
            // Update existing records to have 'active' status (in case default wasn't applied)
            try {
                $this->db->table('materials')
                         ->where('status IS NULL')
                         ->orWhere('status', '')
                         ->update(['status' => 'active']);
            } catch (\Exception $e) {
                // Ignore errors - default value should handle this
            }
        }
    }

    public function down()
    {
        // Check if status column exists before dropping
        try {
            $result = $this->db->query("SHOW COLUMNS FROM materials LIKE 'status'");
            $statusExists = $result->getNumRows() > 0;
        } catch (\Exception $e) {
            $statusExists = false;
        }

        if ($statusExists) {
            $this->forge->dropColumn('materials', ['status']);
        }
    }
}

