<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixAuditLogsUserIdNullable extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('audit_logs', [
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
        $this->forge->modifyColumn('audit_logs', [
            'user_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
                'constraint' => null,
            ],
        ]);
    }
}
