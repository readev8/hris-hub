<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlueprints extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'improvement_id'=> ['type' => 'BIGINT', 'unsigned' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => 255],
            'description'   => ['type' => 'TEXT', 'null' => true],
            'status'        => ['type' => 'TINYINT', 'default' => 0],
            'approver_id'   => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_by'    => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at'    => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'updated_at'    => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addUniqueKey(['improvement_id']);
        $this->forge->addForeignKey('improvement_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('approver_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('blueprints');
    }

    public function down()
    {
        $this->forge->dropTable('blueprints');
    }
}
