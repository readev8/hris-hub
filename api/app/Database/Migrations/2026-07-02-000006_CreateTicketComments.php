<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

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
        $this->forge->addForeignKey('ticket_id', 'tickets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ticket_comments');
    }

    public function down()
    {
        $this->forge->dropTable('ticket_comments');
    }
}
