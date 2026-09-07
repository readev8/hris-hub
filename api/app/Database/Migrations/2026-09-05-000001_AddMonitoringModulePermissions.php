<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class AddMonitoringModulePermissions extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');

        $exists = $this->db->table(Tables::ACCESS_MODULES)->where('slug', 'monitoring')->countAllResults();
        if ($exists === 0) {
            $this->db->table(Tables::ACCESS_MODULES)->insert([
                'name' => 'Monitoring', 'slug' => 'monitoring',
                'icon' => 'fas fa-chart-line', 'sort_order' => 8,
            ]);
        }

        $grants = [
            5 => ['can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0], // admin
            4 => ['can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0], // it_manager
            3 => ['can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0], // dept_head
        ];
        foreach ($grants as $roleId => $perm) {
            $has = $this->db->table(Tables::ROLE_PERMISSIONS)
                ->where('role_id', $roleId)->where('module_slug', 'monitoring')->countAllResults();
            if ($has === 0) {
                $this->db->table(Tables::ROLE_PERMISSIONS)->insert(array_merge(
                    ['module_slug' => 'monitoring', 'role_id' => $roleId, 'created_at' => $now, 'updated_at' => $now],
                    $perm
                ));
            }
        }
    }

    public function down()
    {
        $this->db->table(Tables::ROLE_PERMISSIONS)->where('module_slug', 'monitoring')->delete();
        $this->db->table(Tables::ACCESS_MODULES)->where('slug', 'monitoring')->delete();
    }
}
