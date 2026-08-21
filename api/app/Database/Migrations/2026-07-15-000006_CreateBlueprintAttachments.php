<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class CreateBlueprintAttachments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'blueprint_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'module_id'    => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'default' => null],
            'section_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'section_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'uploaded_by'  => ['type' => 'BIGINT', 'unsigned' => true],
            'filename'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'stored_name'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'mime_type'    => ['type' => 'VARCHAR', 'constraint' => 100],
            'file_size'    => ['type' => 'INT', 'unsigned' => true],
            'created_at'   => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('blueprint_id');
        $this->forge->addKey('module_id');
        $this->forge->addKey('section_type');
        $this->forge->addForeignKey('blueprint_id', Tables::BLUEPRINTS, 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('module_id', Tables::BLUEPRINT_MODULES, 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('uploaded_by', Tables::USERS, 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable(Tables::BLUEPRINT_ATTACHMENTS);
    }

    public function down()
    {
        $this->forge->dropTable(Tables::BLUEPRINT_ATTACHMENTS);
    }
}
