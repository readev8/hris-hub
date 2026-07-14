<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProjectManagementSeeder extends Seeder
{
    public function run()
    {
        // Users (one per role) — no local password, auth via myhr/plus
        $users = [
            ['user_id' => 'USR-001', 'full_name' => 'Siska Developer', 'email' => 'developer@pm.test', 'role_id' => 1, 'is_active' => 1],
            ['user_id' => 'USR-002', 'full_name' => 'Rina Requester',  'email' => 'requester@pm.test', 'role_id' => 2, 'is_active' => 1],
            ['user_id' => 'USR-003', 'full_name' => 'Budi DeptHead',   'email' => 'depthead@pm.test', 'role_id' => 3, 'is_active' => 1],
            ['user_id' => 'USR-004', 'full_name' => 'Andi ITManager',  'email' => 'itmanager@pm.test', 'role_id' => 4, 'is_active' => 1],
            ['user_id' => 'USR-005', 'full_name' => 'Admin Sistem',    'email' => 'admin@pm.test', 'role_id' => 5, 'is_active' => 1],
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
