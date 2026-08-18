<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class CreateUsers extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'full_name'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'password'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'role'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->addKey('role');
        $this->forge->createTable(Tables::USERS);
    }

    public function down()
    {
        $this->forge->dropTable(Tables::USERS);
    }
}
