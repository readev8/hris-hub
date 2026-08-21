<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class CreateApiKeys extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'api_key'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('api_key');
        $this->forge->createTable(Tables::API_KEYS);
    }

    public function down()
    {
        $this->forge->dropTable(Tables::API_KEYS);
    }
}
