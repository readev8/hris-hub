<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropLegacyRoleColumn extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('users', 'role');
    }

    public function down()
    {
        $this->forge->addColumn('users', [
            'role' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'email'],
        ]);
    }
}
