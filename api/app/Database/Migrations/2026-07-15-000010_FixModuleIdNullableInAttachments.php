<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixModuleIdNullableInAttachments extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('blueprint_attachments', [
            'module_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'default' => null],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('blueprint_attachments', [
            'module_id' => ['type' => 'BIGINT', 'unsigned' => true],
        ]);
    }
}
