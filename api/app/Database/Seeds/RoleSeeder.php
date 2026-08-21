<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Config\Tables;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'Developer',  'slug' => 'developer',  'description' => 'Development team member', 'is_system' => 1, 'is_active' => 1],
            ['name' => 'Requester',  'slug' => 'requester',  'description' => 'End user who submits tickets', 'is_system' => 1, 'is_active' => 1],
            ['name' => 'Dept Head',  'slug' => 'dept_head',  'description' => 'Department head approver', 'is_system' => 1, 'is_active' => 1],
            ['name' => 'IT Manager', 'slug' => 'it_manager', 'description' => 'IT department manager', 'is_system' => 1, 'is_active' => 1],
            ['name' => 'Admin',      'slug' => 'admin',      'description' => 'Full system administrator', 'is_system' => 1, 'is_active' => 1],
        ];

        foreach ($roles as $role) {
            $role['created_at'] = date('Y-m-d H:i:s');
            $role['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table(Tables::ROLES)->insert($role);
        }
    }
}
