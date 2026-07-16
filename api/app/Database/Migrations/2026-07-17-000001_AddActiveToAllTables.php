<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddActiveToAllTables extends Migration
{
    public function up()
    {
        $tables = [
            'projects',
            'approval_requests',
            'project_comments',
            'tickets',
            'ticket_comments',
            'api_keys',
            'ticket_attachments',
            'master_projects',
            'modules',
            'pages',
            'project_attachments',
            'roles',
            'access_modules',
            'role_permissions',
            'blueprints',
            'blueprint_modules',
            'blueprint_business_scenarios',
            'blueprint_design_pages',
            'blueprint_page_specifications',
            'blueprint_attachments',
            'blueprint_approval_requests',
            'blueprint_comments',
        ];

        foreach ($tables as $table) {
            if (!$this->db->fieldExists('active', $table)) {
                $this->forge->addColumn($table, [
                    'active' => [
                        'type'       => 'TINYINT',
                        'constraint' => 1,
                        'unsigned'   => true,
                        'default'    => 0,
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        $tables = [
            'projects',
            'approval_requests',
            'project_comments',
            'tickets',
            'ticket_comments',
            'api_keys',
            'ticket_attachments',
            'master_projects',
            'modules',
            'pages',
            'project_attachments',
            'roles',
            'access_modules',
            'role_permissions',
            'blueprints',
            'blueprint_modules',
            'blueprint_business_scenarios',
            'blueprint_design_pages',
            'blueprint_page_specifications',
            'blueprint_attachments',
            'blueprint_approval_requests',
            'blueprint_comments',
        ];

        foreach ($tables as $table) {
            $this->forge->dropColumn($table, 'active');
        }
    }
}
