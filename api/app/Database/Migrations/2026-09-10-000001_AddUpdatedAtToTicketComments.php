<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class AddUpdatedAtToTicketComments extends Migration
{
    public function up()
    {
        $fields = [
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null, 'after' => 'created_at'],
        ];
        $this->forge->addColumn(Tables::TICKET_COMMENTS, $fields);
    }

    public function down()
    {
        $this->forge->dropColumn(Tables::TICKET_COMMENTS, 'updated_at');
    }
}
