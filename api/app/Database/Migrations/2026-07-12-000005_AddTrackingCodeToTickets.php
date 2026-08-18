<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class AddTrackingCodeToTickets extends Migration
{
    public function up()
    {
        $this->forge->addColumn(Tables::TICKETS, [
            'tracking_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);
        $this->forge->addUniqueKey(Tables::TICKETS, 'tracking_code');
        $this->forge->modifyColumn(Tables::TICKETS, [
            'tracking_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'unique'     => true,
            ],
        ]);

        // Generate tracking codes for existing tickets
        $db = \Config\Database::connect();
        $tickets = $db->table(Tables::TICKETS)->where('tracking_code IS NULL')->get()->getResultArray();
        foreach ($tickets as $t) {
            $code = 'TKT-' . date('Ymd', strtotime($t['created_at'])) . '-' . strtoupper(bin2hex(random_bytes(2)));
            $db->table(Tables::TICKETS)->where('id', $t['id'])->update(['tracking_code' => $code]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn(Tables::TICKETS, 'tracking_code');
    }
}
