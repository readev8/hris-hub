<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class CreateProjectAttachments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'project_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'uploaded_by' => ['type' => 'BIGINT', 'unsigned' => true],
            'filename'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'stored_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'mime_type'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'file_size'   => ['type' => 'INT', 'unsigned' => true],
            'created_at'  => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('project_id', Tables::PROJECTS, 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('uploaded_by', Tables::USERS, 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable(Tables::PROJECT_ATTACHMENTS);
    }

    public function down()
    {
        $this->forge->dropTable(Tables::PROJECT_ATTACHMENTS);
    }
}
