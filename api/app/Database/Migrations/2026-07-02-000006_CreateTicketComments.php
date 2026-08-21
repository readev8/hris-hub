<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class CreateTicketComments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'ticket_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'user_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'content'    => ['type' => 'TEXT'],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['ticket_id']);
        $this->forge->addForeignKey('ticket_id', Tables::TICKETS, 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable(Tables::TICKET_COMMENTS);
    }

    public function down()
    {
        $this->forge->dropTable(Tables::TICKET_COMMENTS);
    }
}
