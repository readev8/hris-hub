<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApproverIdToTickets extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tickets', [
            'approver_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'creator_id'],
        ]);
        $this->forge->addForeignKey('approver_id', 'users', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey('tickets', 'tickets_approver_id_foreign');
        $this->forge->dropColumn('tickets', 'approver_id');
    }
}
