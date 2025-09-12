<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        $model = new UserModel();

        $users = [
            [
                'name'     => 'Admin User',
                'email'    => 'admin@example.com',
                'password' => 'admin123',
                'role'     => 'admin'  // Admin role
            ],
            [
                'name'     => 'Regular User',
                'email'    => 'user@example.com',
                'password' => 'user123',  // Regular user role
                'role'     => 'user'      // User role
            ]
        ];

        foreach ($users as $user) {
            // Save directly without validation to prevent any insertion errors
            $model->skipValidation(true)->save($user);
        }

        echo "Users seeded successfully!";
    }
}
