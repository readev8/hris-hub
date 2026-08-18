<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class CreateRolePermissions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'role_id'      => ['type' => 'BIGINT', 'unsigned' => true],
            'module_slug'  => ['type' => 'VARCHAR', 'constraint' => 50],
            'can_view'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'can_create'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'can_update'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'can_delete'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'can_approve'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'   => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'updated_at'   => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('role_id');
        $this->forge->addKey('module_slug');
        $this->forge->addUniqueKey(['role_id', 'module_slug']);
        $this->forge->addForeignKey('role_id', Tables::ROLES, 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable(Tables::ROLE_PERMISSIONS);
    }

    public function down()
    {
        $this->forge->dropTable(Tables::ROLE_PERMISSIONS);
    }
}
