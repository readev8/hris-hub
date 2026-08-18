<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class AddProjectsFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn(Tables::PROJECTS, [
            'category'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'business_case'],
            'assignee_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'priority'],
            'page_id'     => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'assignee_id'],
            'target_date' => ['type' => 'DATE', 'null' => true, 'after' => 'page_id'],
        ]);

        $this->forge->addForeignKey('assignee_id', Tables::USERS, 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('page_id', Tables::PAGES, 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey(Tables::PROJECTS, 'projects_assignee_id_foreign');
        $this->forge->dropForeignKey(Tables::PROJECTS, 'projects_page_id_foreign');
        $this->forge->dropColumn(Tables::PROJECTS, ['category', 'assignee_id', 'page_id', 'target_date']);
    }
}
