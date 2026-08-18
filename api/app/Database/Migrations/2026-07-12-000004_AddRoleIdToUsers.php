<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class AddRoleIdToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn(Tables::USERS, [
            'role_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'role'],
        ]);
        $this->forge->addKey('role_id');
        $this->forge->addForeignKey('role_id', Tables::ROLES, 'id', 'SET NULL', 'CASCADE');
        $this->forge->modifyColumn(Tables::USERS, [
            'role_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'role'],
        ]);

        // Migrate existing role int values to role_id
        $this->db->query("UPDATE " . Tables::USERS . " SET role_id = role WHERE role IS NOT NULL");
    }

    public function down()
    {
        $this->forge->dropColumn(Tables::USERS, 'role_id');
    }
}
