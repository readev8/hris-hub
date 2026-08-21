<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class AddActiveToAllTables extends Migration
{
    public function up()
    {
        $tables = [
            Tables::PROJECTS,
            Tables::APPROVAL_REQUESTS,
            Tables::PROJECT_COMMENTS,
            Tables::TICKETS,
            Tables::TICKET_COMMENTS,
            Tables::API_KEYS,
            Tables::TICKET_ATTACHMENTS,
            Tables::MASTER_PROJECTS,
            Tables::MODULES,
            Tables::PAGES,
            Tables::PROJECT_ATTACHMENTS,
            Tables::ROLES,
            Tables::ACCESS_MODULES,
            Tables::ROLE_PERMISSIONS,
            Tables::BLUEPRINTS,
            Tables::BLUEPRINT_MODULES,
            Tables::BLUEPRINT_BUSINESS_SCENARIOS,
            Tables::BLUEPRINT_DESIGN_PAGES,
            Tables::BLUEPRINT_PAGE_SPECIFICATIONS,
            Tables::BLUEPRINT_ATTACHMENTS,
            Tables::BLUEPRINT_APPROVAL_REQUESTS,
            Tables::BLUEPRINT_COMMENTS,
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
            Tables::PROJECTS,
            Tables::APPROVAL_REQUESTS,
            Tables::PROJECT_COMMENTS,
            Tables::TICKETS,
            Tables::TICKET_COMMENTS,
            Tables::API_KEYS,
            Tables::TICKET_ATTACHMENTS,
            Tables::MASTER_PROJECTS,
            Tables::MODULES,
            Tables::PAGES,
            Tables::PROJECT_ATTACHMENTS,
            Tables::ROLES,
            Tables::ACCESS_MODULES,
            Tables::ROLE_PERMISSIONS,
            Tables::BLUEPRINTS,
            Tables::BLUEPRINT_MODULES,
            Tables::BLUEPRINT_BUSINESS_SCENARIOS,
            Tables::BLUEPRINT_DESIGN_PAGES,
            Tables::BLUEPRINT_PAGE_SPECIFICATIONS,
            Tables::BLUEPRINT_ATTACHMENTS,
            Tables::BLUEPRINT_APPROVAL_REQUESTS,
            Tables::BLUEPRINT_COMMENTS,
        ];

        foreach ($tables as $table) {
            $this->forge->dropColumn($table, 'active');
        }
    }
}
