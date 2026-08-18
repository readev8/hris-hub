<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class AddApprovalToTickets extends Migration
{
    public function up()
    {
        $this->forge->addColumn(Tables::APPROVAL_REQUESTS, [
            'ticket_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'project_id'],
        ]);
        $this->forge->addKey('ticket_id');
        $this->forge->addForeignKey('ticket_id', Tables::TICKETS, 'id', 'CASCADE', 'SET NULL');

        $this->forge->addColumn(Tables::TICKETS, [
            'needs_approval' => ['type' => 'TINYINT', 'default' => 0, 'after' => 'type'],
        ]);
    }

    public function down()
    {
        $this->forge->dropForeignKey(Tables::APPROVAL_REQUESTS, 'approval_requests_ticket_id_foreign');
        $this->forge->dropColumn(Tables::APPROVAL_REQUESTS, 'ticket_id');
        $this->forge->dropColumn(Tables::TICKETS, 'needs_approval');
    }
}
