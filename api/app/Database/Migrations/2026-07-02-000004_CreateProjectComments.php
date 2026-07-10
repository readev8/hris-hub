<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProjectComments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'project_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'user_id'    => ['type' => 'BIGINT', 'unsigned' => true],
            'content'    => ['type' => 'TEXT'],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['project_id']);
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('project_comments');
    }

    public function down()
    {
        $this->forge->dropTable('project_comments');
    }
}
