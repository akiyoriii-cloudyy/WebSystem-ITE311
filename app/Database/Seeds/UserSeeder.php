<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Define user data
        $data = [
            [
                'name'       => 'Admin User',
                'email'      => 'admin@example.com',
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'role'       => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Test User',
                'email'      => 'test@example.com',
                'password'   => password_hash('test123', PASSWORD_DEFAULT),
                'role'       => 'student',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert data into users table
        $this->db->table('users')->insertBatch($data);
    }
}
