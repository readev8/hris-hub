<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class AddPageIdToTickets extends Migration
{
    public function up()
    {
        $this->forge->addColumn(Tables::TICKETS, [
            'page_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addForeignKey('page_id', Tables::PAGES, 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey(Tables::TICKETS, 'tickets_page_id_foreign');
        $this->forge->dropColumn(Tables::TICKETS, 'page_id');
    }
}
