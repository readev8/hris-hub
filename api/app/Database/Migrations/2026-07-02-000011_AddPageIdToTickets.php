<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPageIdToTickets extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tickets', [
            'page_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addForeignKey('page_id', 'pages', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey('tickets', 'tickets_page_id_foreign');
        $this->forge->dropColumn('tickets', 'page_id');
    }
}
