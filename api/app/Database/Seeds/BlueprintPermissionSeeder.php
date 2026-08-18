<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Config\Tables;

class BlueprintPermissionSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $roleMap = [
            'developer'  => 1,
            'requester'  => 2,
            'dept_head'  => 3,
            'it_manager' => 4,
            'admin'      => 5,
        ];

        $permissions = [
            'admin' => [
                ['can_view' => 1, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
            ],
            'it_manager' => [
                ['can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 1],
            ],
            'dept_head' => [
                ['can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 1],
            ],
            'developer' => [
                ['can_view' => 1, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 0, 'can_approve' => 0],
            ],
            'requester' => [
                ['can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
            ],
        ];

        foreach ($permissions as $slug => $perms) {
            $roleId = $roleMap[$slug];
            foreach ($perms as $perm) {
                $this->db->table(Tables::ROLE_PERMISSIONS)->insert([
                    'role_id'     => $roleId,
                    'module_slug' => 'blueprints',
                    'can_view'    => $perm['can_view'],
                    'can_create'  => $perm['can_create'],
                    'can_update'  => $perm['can_update'],
                    'can_delete'  => $perm['can_delete'],
                    'can_approve' => $perm['can_approve'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        }
    }
}
