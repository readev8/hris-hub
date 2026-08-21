<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class CreateBlueprintPageSpecifications extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'module_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'field_name'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'data'          => ['type' => 'TEXT', 'null' => true],
            'objective'     => ['type' => 'TEXT', 'null' => true],
            'initial_data'  => ['type' => 'TEXT', 'null' => true],
            'condition'     => ['type' => 'TEXT', 'null' => true],
            'validation'    => ['type' => 'TEXT', 'null' => true],
            'input_display' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'datatype'      => ['type' => 'VARCHAR', 'constraint' => 50],
            'control_type'  => ['type' => 'VARCHAR', 'constraint' => 50],
            'ux'            => ['type' => 'TEXT', 'null' => true],
            'sort_order'    => ['type' => 'INT', 'default' => 0],
            'created_at'    => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'updated_at'    => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('module_id');
        $this->forge->addForeignKey('module_id', Tables::BLUEPRINT_MODULES, 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable(Tables::BLUEPRINT_PAGE_SPECIFICATIONS);
    }

    public function down()
    {
        $this->forge->dropTable(Tables::BLUEPRINT_PAGE_SPECIFICATIONS);
    }
}
