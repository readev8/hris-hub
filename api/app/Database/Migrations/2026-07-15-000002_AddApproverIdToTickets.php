<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class AddApproverIdToTickets extends Migration
{
    public function up()
    {
        $this->forge->addColumn(Tables::TICKETS, [
            'approver_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'creator_id'],
        ]);
        $this->forge->addForeignKey('approver_id', Tables::USERS, 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey(Tables::TICKETS, 'tickets_approver_id_foreign');
        $this->forge->dropColumn(Tables::TICKETS, 'approver_id');
    }
}
