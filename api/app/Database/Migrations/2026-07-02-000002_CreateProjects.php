<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class CreateProjects extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'name'             => ['type' => 'VARCHAR', 'constraint' => 255],
            'description'      => ['type' => 'TEXT', 'null' => true],
            'business_case'    => ['type' => 'TEXT', 'null' => true],
            'priority'         => ['type' => 'TINYINT', 'default' => 1],
            'status'           => ['type' => 'TINYINT', 'default' => 0],
            'approval_workflow'=> ['type' => 'JSON', 'null' => true],
            'dept_head_id'     => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'it_manager_id'    => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'estimated_start'  => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'estimated_end'    => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'created_by'       => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at'       => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'updated_at'       => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addKey('priority');
        $this->forge->addForeignKey('created_by', Tables::USERS, 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable(Tables::PROJECTS);
    }

    public function down()
    {
        $this->forge->dropTable(Tables::PROJECTS);
    }
}
