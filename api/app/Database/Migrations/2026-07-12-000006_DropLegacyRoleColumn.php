<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class DropLegacyRoleColumn extends Migration
{
    public function up()
    {
        $this->forge->dropColumn(Tables::USERS, 'role');
    }

    public function down()
    {
        $this->forge->addColumn(Tables::USERS, [
            'role' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'email'],
        ]);
    }
}
