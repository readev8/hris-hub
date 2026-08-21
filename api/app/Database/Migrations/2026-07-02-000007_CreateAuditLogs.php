<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class CreateAuditLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'entity_type' => ['type' => 'VARCHAR', 'constraint' => 255],
            'entity_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'action'      => ['type' => 'VARCHAR', 'constraint' => 50],
            'old_values'  => ['type' => 'JSON', 'null' => true],
            'new_values'  => ['type' => 'JSON', 'null' => true],
            'created_at'  => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id']);
        $this->forge->addKey(['entity_type', 'entity_id']);
        $this->forge->addForeignKey('user_id', Tables::USERS, 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable(Tables::AUDIT_LOGS);
    }

    public function down()
    {
        $this->forge->dropTable(Tables::AUDIT_LOGS);
    }
}
