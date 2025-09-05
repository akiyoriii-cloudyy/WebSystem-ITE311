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
                'role'     => 'admin'
            ],
            [
                'name'     => 'Instructor User',
                'email'    => 'instructor@example.com',
                'password' => 'instructor123', 
                'role'     => 'instructor'
            ],
            [
                'name'     => 'Student User',
                'email'    => 'student@example.com',
                'password' => 'student123', 
                'role'     => 'student'
            ]
        ];

        foreach ($users as $user) {
            // Save directly without validation to prevent any insertion errors
            $model->skipValidation(true)->save($user);
        }

        echo "Users seeded successfully!";
    }
}
