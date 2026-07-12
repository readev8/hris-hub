<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProjectsFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('projects', [
            'category'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'business_case'],
            'assignee_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'priority'],
            'page_id'     => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'assignee_id'],
            'target_date' => ['type' => 'DATE', 'null' => true, 'after' => 'page_id'],
        ]);

        $this->forge->addForeignKey('assignee_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('page_id', 'pages', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey('projects', 'projects_assignee_id_foreign');
        $this->forge->dropForeignKey('projects', 'projects_page_id_foreign');
        $this->forge->dropColumn('projects', ['category', 'assignee_id', 'page_id', 'target_date']);
    }
}
