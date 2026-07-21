<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsAnonymousToTickets extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('is_anonymous', 'tickets')) {
            $this->forge->addColumn('tickets', [
                'is_anonymous' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'unsigned'   => true,
                    'null'       => false,
                    'default'    => 0,
                    'after'      => 'creator_id',
                ],
            ]);
            $this->db->query('ALTER TABLE tickets ADD INDEX idx_anon_active (is_anonymous, active)');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('is_anonymous', 'tickets')) {
            $this->db->query('ALTER TABLE tickets DROP INDEX idx_anon_active');
            $this->forge->dropColumn('tickets', 'is_anonymous');
        }
    }
}
