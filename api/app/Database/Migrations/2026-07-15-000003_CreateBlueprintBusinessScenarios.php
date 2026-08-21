<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class CreateBlueprintBusinessScenarios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'module_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'title'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'sort_order'  => ['type' => 'INT', 'default' => 0],
            'created_at'  => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'updated_at'  => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('module_id');
        $this->forge->addForeignKey('module_id', Tables::BLUEPRINT_MODULES, 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable(Tables::BLUEPRINT_BUSINESS_SCENARIOS);
    }

    public function down()
    {
        $this->forge->dropTable(Tables::BLUEPRINT_BUSINESS_SCENARIOS);
    }
}
