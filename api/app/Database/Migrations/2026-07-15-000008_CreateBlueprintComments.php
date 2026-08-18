<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class CreateBlueprintComments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'blueprint_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'user_id'      => ['type' => 'BIGINT', 'unsigned' => true],
            'content'      => ['type' => 'TEXT'],
            'created_at'   => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('blueprint_id');
        $this->forge->addForeignKey('blueprint_id', Tables::BLUEPRINTS, 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', Tables::USERS, 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable(Tables::BLUEPRINT_COMMENTS);
    }

    public function down()
    {
        $this->forge->dropTable(Tables::BLUEPRINT_COMMENTS);
    }
}
