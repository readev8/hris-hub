<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTrackingCodeToTickets extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tickets', [
            'tracking_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);
        $this->forge->addUniqueKey('tickets', 'tracking_code');
        $this->forge->modifyColumn('tickets', [
            'tracking_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'unique'     => true,
            ],
        ]);

        // Generate tracking codes for existing tickets
        $db = \Config\Database::connect();
        $tickets = $db->table('tickets')->where('tracking_code IS NULL')->get()->getResultArray();
        foreach ($tickets as $t) {
            $code = 'TKT-' . date('Ymd', strtotime($t['created_at'])) . '-' . strtoupper(bin2hex(random_bytes(2)));
            $db->table('tickets')->where('id', $t['id'])->update(['tracking_code' => $code]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('tickets', 'tracking_code');
    }
}
