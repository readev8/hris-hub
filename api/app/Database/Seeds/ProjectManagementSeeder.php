<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProjectManagementSeeder extends Seeder
{
    public function run()
    {
        // Users (one per role)
        $users = [
            ['full_name' => 'Siska Developer', 'email' => 'developer@pm.test', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'role_id' => 1, 'is_active' => 1],
            ['full_name' => 'Rina Requester',  'email' => 'requester@pm.test', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'role_id' => 2, 'is_active' => 1],
            ['full_name' => 'Budi DeptHead',   'email' => 'depthead@pm.test', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'role_id' => 3, 'is_active' => 1],
            ['full_name' => 'Andi ITManager',  'email' => 'itmanager@pm.test', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'role_id' => 4, 'is_active' => 1],
            ['full_name' => 'Admin Sistem',    'email' => 'admin@pm.test', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'role_id' => 5, 'is_active' => 1],
        ];
        $this->db->table('users')->insertBatch($users);

        // API key for web/ app
        $this->db->table('api_keys')->insert([
            'api_key' => '0a99ba4084fbfd7c59188477d177f8daaf45b742a8dfadf47aecb114aace8341',
            'name'    => 'Web App Service Key',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
