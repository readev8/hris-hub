<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Config\Tables;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // Role slugs mapped to IDs (1-5 based on insert order)
        $roleMap = [
            'developer'  => 1,
            'requester'  => 2,
            'dept_head'  => 3,
            'it_manager' => 4,
            'admin'      => 5,
        ];

        $permissions = [
            'admin' => [
                ['module_slug' => 'dashboard',       'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'tickets',         'can_view' => 1, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
                ['module_slug' => 'improvements',    'can_view' => 1, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 1],
                ['module_slug' => 'approvals',       'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 1],
                ['module_slug' => 'master_projects', 'can_view' => 1, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 0],
                ['module_slug' => 'users',           'can_view' => 1, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 0],
                ['module_slug' => 'roles',           'can_view' => 1, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 0],
                ['module_slug' => 'monitoring',    'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
            ],
            'it_manager' => [
                ['module_slug' => 'dashboard',       'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'tickets',         'can_view' => 1, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 0, 'can_approve' => 1],
                ['module_slug' => 'improvements',    'can_view' => 1, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 0, 'can_approve' => 1],
                ['module_slug' => 'approvals',       'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 1],
                ['module_slug' => 'master_projects', 'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'users',           'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'roles',           'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'monitoring',    'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
            ],
            'dept_head' => [
                ['module_slug' => 'dashboard',       'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'tickets',         'can_view' => 1, 'can_create' => 1, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 1],
                ['module_slug' => 'improvements',    'can_view' => 1, 'can_create' => 1, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 1],
                ['module_slug' => 'approvals',       'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 1],
                ['module_slug' => 'master_projects', 'can_view' => 0, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'users',           'can_view' => 0, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'roles',           'can_view' => 0, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'monitoring',    'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
            ],
            'developer' => [
                ['module_slug' => 'dashboard',       'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'tickets',         'can_view' => 1, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'improvements',    'can_view' => 1, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'approvals',       'can_view' => 0, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'master_projects', 'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'users',           'can_view' => 0, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'roles',           'can_view' => 0, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
            ],
            'requester' => [
                ['module_slug' => 'dashboard',       'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'tickets',         'can_view' => 1, 'can_create' => 1, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'improvements',    'can_view' => 1, 'can_create' => 1, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'approvals',       'can_view' => 0, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'master_projects', 'can_view' => 0, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'users',           'can_view' => 0, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
                ['module_slug' => 'roles',           'can_view' => 0, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
            ],
        ];

        foreach ($permissions as $slug => $perms) {
            $roleId = $roleMap[$slug];
            foreach ($perms as $perm) {
                $perm['role_id'] = $roleId;
                $perm['created_at'] = $now;
                $perm['updated_at'] = $now;
                $this->db->table(Tables::ROLE_PERMISSIONS)->insert($perm);
            }
        }
    }
}
