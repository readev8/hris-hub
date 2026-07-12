<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

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
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('uploaded_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('project_attachments');
    }

    public function down()
    {
        $this->forge->dropTable('project_attachments');
    }
}
