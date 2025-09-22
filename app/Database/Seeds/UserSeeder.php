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
                'password' => password_hash('admin123', PASSWORD_DEFAULT), // ✅ hashed password
                'role'     => 'admin'
            ],
            [
                'name'     => 'Teacher User',
                'email'    => 'teacher@example.com',
                'password' => password_hash('teacher123', PASSWORD_DEFAULT), // ✅ hashed password
                'role'     => 'teacher'
            ],
            [
                'name'     => 'Student User',
                'email'    => 'student@example.com',
                'password' => password_hash('student123', PASSWORD_DEFAULT), // ✅ hashed password
                'role'     => 'student'
            ],
        ];

        foreach ($users as $user) {
            $model->skipValidation(true)->save($user);
        }

        echo "Users seeded successfully!";
    }
}
