<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTicketAttachments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'ticket_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'comment_id'  => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'uploaded_by' => ['type' => 'BIGINT', 'unsigned' => true],
            'filename'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'stored_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'mime_type'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'file_size'   => ['type' => 'INT', 'unsigned' => true],
            'created_at'  => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('ticket_id', 'tickets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('comment_id', 'ticket_comments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('uploaded_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ticket_attachments');
    }

    public function down()
    {
        $this->forge->dropTable('ticket_attachments');
    }
}
