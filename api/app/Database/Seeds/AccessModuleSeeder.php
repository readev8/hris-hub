<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AccessModuleSeeder extends Seeder
{
    public function run()
    {
        $modules = [
            ['name' => 'Dashboard',       'slug' => 'dashboard',       'icon' => 'fas fa-th-large',    'sort_order' => 1],
            ['name' => 'Tickets',         'slug' => 'tickets',         'icon' => 'fas fa-ticket',       'sort_order' => 2],
            ['name' => 'Improvements',    'slug' => 'improvements',    'icon' => 'fas fa-rocket',       'sort_order' => 3],
            ['name' => 'Approvals',       'slug' => 'approvals',       'icon' => 'fas fa-check-circle', 'sort_order' => 4],
            ['name' => 'Master Projects', 'slug' => 'master_projects', 'icon' => 'fas fa-folder-tree',  'sort_order' => 5],
            ['name' => 'Users',           'slug' => 'users',           'icon' => 'fas fa-users',        'sort_order' => 6],
            ['name' => 'Roles',           'slug' => 'roles',           'icon' => 'fas fa-user-shield',  'sort_order' => 7],
        ];

        foreach ($modules as $mod) {
            $this->db->table('access_modules')->insert($mod);
        }
    }
}
