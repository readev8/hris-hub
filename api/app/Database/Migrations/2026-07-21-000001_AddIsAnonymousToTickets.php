<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class AddIsAnonymousToTickets extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('is_anonymous', Tables::TICKETS)) {
            $this->forge->addColumn(Tables::TICKETS, [
                'is_anonymous' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'unsigned'   => true,
                    'null'       => false,
                    'default'    => 0,
                    'after'      => 'creator_id',
                ],
            ]);
            $this->db->query('ALTER TABLE ' . Tables::TICKETS . ' ADD INDEX idx_anon_active (is_anonymous, active)');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('is_anonymous', Tables::TICKETS)) {
            $this->db->query('ALTER TABLE ' . Tables::TICKETS . ' DROP INDEX idx_anon_active');
            $this->forge->dropColumn(Tables::TICKETS, 'is_anonymous');
        }
    }
}
