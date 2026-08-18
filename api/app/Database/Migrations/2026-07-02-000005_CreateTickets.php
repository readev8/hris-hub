<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class CreateTickets extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'title'          => ['type' => 'VARCHAR', 'constraint' => 255],
            'description'    => ['type' => 'TEXT', 'null' => true],
            'type'           => ['type' => 'TINYINT', 'default' => 0],
            'priority'       => ['type' => 'TINYINT', 'default' => 1],
            'status'         => ['type' => 'TINYINT', 'default' => 0],
            'closed_at'      => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'assignee_id'    => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'creator_id'     => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'resolution_note'=> ['type' => 'TEXT', 'null' => true],
            'rejection_note' => ['type' => 'TEXT', 'null' => true],
            'due_date'       => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'created_at'     => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'updated_at'     => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addKey('type');
        $this->forge->addKey('priority');
        $this->forge->addKey('closed_at');
        $this->forge->addKey(['assignee_id']);
        $this->forge->addForeignKey('assignee_id', Tables::USERS, 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('creator_id', Tables::USERS, 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable(Tables::TICKETS);
    }

    public function down()
    {
        $this->forge->dropTable(Tables::TICKETS);
    }
}
