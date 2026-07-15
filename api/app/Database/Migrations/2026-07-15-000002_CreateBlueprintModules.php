<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlueprintModules extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'blueprint_id'=> ['type' => 'BIGINT', 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'sort_order'  => ['type' => 'INT', 'default' => 0],
            'created_at'  => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'updated_at'  => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('blueprint_id');
        $this->forge->addForeignKey('blueprint_id', 'blueprints', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('blueprint_modules');
    }

    public function down()
    {
        $this->forge->dropTable('blueprint_modules');
    }
}
