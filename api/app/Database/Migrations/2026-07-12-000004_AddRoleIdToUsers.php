<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoleIdToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'role_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'role'],
        ]);
        $this->forge->addKey('role_id');
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'SET NULL', 'CASCADE');
        $this->forge->modifyColumn('users', [
            'role_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'role'],
        ]);

        // Migrate existing role int values to role_id
        $this->db->query("UPDATE users SET role_id = role WHERE role IS NOT NULL");
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'role_id');
    }
}
