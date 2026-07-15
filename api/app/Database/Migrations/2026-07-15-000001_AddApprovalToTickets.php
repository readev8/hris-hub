<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApprovalToTickets extends Migration
{
    public function up()
    {
        $this->forge->addColumn('approval_requests', [
            'ticket_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'project_id'],
        ]);
        $this->forge->addKey('ticket_id');
        $this->forge->addForeignKey('ticket_id', 'tickets', 'id', 'CASCADE', 'SET NULL');

        $this->forge->addColumn('tickets', [
            'needs_approval' => ['type' => 'TINYINT', 'default' => 0, 'after' => 'type'],
        ]);
    }

    public function down()
    {
        $this->forge->dropForeignKey('approval_requests', 'approval_requests_ticket_id_foreign');
        $this->forge->dropColumn('approval_requests', 'ticket_id');
        $this->forge->dropColumn('tickets', 'needs_approval');
    }
}
