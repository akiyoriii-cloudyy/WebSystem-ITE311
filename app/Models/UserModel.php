<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $useAutoIncrement = true;

    protected $returnType    = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name', 'email', 'password', 'role', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;          // auto-fill created_at / updated_at
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Updated validation rules for 'admin' and 'user' roles only
    protected $validationRules = [
        'name'     => 'required|min_length[2]',
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[6]',
        'role'     => 'required|in_list[admin,user]',  // Updated to only allow 'admin' and 'user' roles
    ];

    // Automatically hash password before insert/update
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }
}
