<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class CreateModuleFlows extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // ── flow_connections ─────────────────────────────────────
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'from_module_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'to_module_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'created_by'      => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at'      => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'updated_at'      => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'active'          => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('from_module_id');
        $this->forge->addKey('to_module_id');
        $this->forge->addUniqueKey(['from_module_id', 'to_module_id']);
        $this->forge->addForeignKey('from_module_id', Tables::MODULES, 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('to_module_id', Tables::MODULES, 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', Tables::USERS, 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable(Tables::FLOW_CONNECTIONS);

        // ── flow_node_positions ──────────────────────────────────
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'module_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'pos_x'       => ['type' => 'INT', 'default' => 0],
            'pos_y'       => ['type' => 'INT', 'default' => 0],
            'created_by'  => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at'  => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'updated_at'  => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'active'      => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('module_id');
        $this->forge->addForeignKey('module_id', Tables::MODULES, 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', Tables::USERS, 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable(Tables::FLOW_NODE_POSITIONS);

        // ── Register access module ───────────────────────────────
        $exists = $db->table(Tables::ACCESS_MODULES)
            ->where('slug', 'module_flows')
            ->countAllResults();

        if ($exists === 0) {
            $db->table(Tables::ACCESS_MODULES)->insert([
                'name'       => 'Module Flows',
                'slug'       => 'module_flows',
                'icon'       => 'fas fa-diagram-project',
                'sort_order' => 9,
            ]);
        }

        // ── Default role permissions ─────────────────────────────
        // role ids: 1=Developer, 2=Requester, 3=Dept Head, 4=IT Manager, 5=Admin
        $perms = [
            ['role_id' => 5, 'can_view' => 1, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 0],
            ['role_id' => 1, 'can_view' => 1, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1, 'can_approve' => 0],
            ['role_id' => 4, 'can_view' => 1, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 0, 'can_approve' => 0],
            ['role_id' => 3, 'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
            ['role_id' => 2, 'can_view' => 1, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0, 'can_approve' => 0],
        ];

        foreach ($perms as $p) {
            $existing = $db->table(Tables::ROLE_PERMISSIONS)
                ->where('role_id', $p['role_id'])
                ->where('module_slug', 'module_flows')
                ->countAllResults();

            if ($existing === 0) {
                $db->table(Tables::ROLE_PERMISSIONS)->insert([
                    'role_id'     => $p['role_id'],
                    'module_slug' => 'module_flows',
                    'can_view'    => $p['can_view'],
                    'can_create'  => $p['can_create'],
                    'can_update'  => $p['can_update'],
                    'can_delete'  => $p['can_delete'],
                    'can_approve' => $p['can_approve'],
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable(Tables::FLOW_NODE_POSITIONS);
        $this->forge->dropTable(Tables::FLOW_CONNECTIONS);

        $db = \Config\Database::connect();
        $db->table(Tables::ROLE_PERMISSIONS)
            ->where('module_slug', 'module_flows')
            ->delete();
        $db->table(Tables::ACCESS_MODULES)
            ->where('slug', 'module_flows')
            ->delete();
    }
}
