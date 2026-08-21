<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class FixAuditLogsUserIdNullable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn(Tables::AUDIT_LOGS, [
            'user_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
                'constraint' => null,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn(Tables::AUDIT_LOGS, [
            'user_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
                'constraint' => null,
            ],
        ]);
    }
}
