<?php

namespace App\Database\Migration;

use CodeIgniter\Database\Migration;

class CreateModuleBlueprintModules extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $this->forge->addField([
            'id'                  => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'module_id'           => ['type' => 'BIGINT', 'unsigned' => true],
            'blueprint_module_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'created_at'          => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('module_id');
        $this->forge->addKey('blueprint_module_id');
        $this->forge->addForeignKey('module_id', 'modules', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('blueprint_module_id', 'blueprint_modules', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('module_blueprint_modules');

        $db->query('
            INSERT INTO module_blueprint_modules (module_id, blueprint_module_id, created_at)
            SELECT id, blueprint_module_id, NOW()
            FROM modules
            WHERE blueprint_module_id IS NOT NULL AND active = 0
        ');
    }

    public function down()
    {
        $this->forge->dropTable('module_blueprint_modules');
    }
}
