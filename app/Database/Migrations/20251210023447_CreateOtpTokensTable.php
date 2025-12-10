<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOtpTokensTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Foreign key to users',
            ],
            'otp_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 6,
                'comment'    => '6-digit OTP code',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'Email address where OTP was sent',
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'comment' => 'OTP expiration time (typically 5-10 minutes)',
            ],
            'is_used' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '1 = Used, 0 = Not used',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey(['user_id', 'otp_code', 'is_used']);
        $this->forge->createTable('otp_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('otp_tokens');
    }
}
