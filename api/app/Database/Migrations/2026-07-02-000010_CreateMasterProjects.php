<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

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
        $this->forge->addForeignKey('created_by', Tables::USERS, 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable(Tables::MASTER_PROJECTS);

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
        $this->forge->addForeignKey('master_project_id', Tables::MASTER_PROJECTS, 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable(Tables::MODULES);

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
        $this->forge->addForeignKey('module_id', Tables::MODULES, 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable(Tables::PAGES);
    }

    public function down()
    {
        $this->forge->dropTable(Tables::PAGES, true);
        $this->forge->dropTable(Tables::MODULES, true);
        $this->forge->dropTable(Tables::MASTER_PROJECTS, true);
    }
}
