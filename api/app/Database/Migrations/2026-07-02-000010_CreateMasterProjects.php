<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMasterProjects extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'status'      => ['type' => 'TINYINT', 'default' => 1],
            'created_by'  => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at'  => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'  => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('master_projects');

        $this->forge->addField([
            'id'                => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'master_project_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'name'              => ['type' => 'VARCHAR', 'constraint' => 255],
            'description'       => ['type' => 'TEXT', 'null' => true],
            'sort_order'        => ['type' => 'INT', 'default' => 0],
            'created_at'        => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'        => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('master_project_id', 'master_projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('modules');

        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'module_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'url_path'    => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'description' => ['type' => 'TEXT', 'null' => true],
            'sort_order'  => ['type' => 'INT', 'default' => 0],
            'created_at'  => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'  => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('module_id', 'modules', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pages');
    }

    public function down()
    {
        $this->forge->dropTable('pages', true);
        $this->forge->dropTable('modules', true);
        $this->forge->dropTable('master_projects', true);
    }
}
