<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class FixModuleIdNullableInAttachments extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn(Tables::BLUEPRINT_ATTACHMENTS, [
            'module_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'default' => null],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn(Tables::BLUEPRINT_ATTACHMENTS, [
            'module_id' => ['type' => 'BIGINT', 'unsigned' => true],
        ]);
    }
}
