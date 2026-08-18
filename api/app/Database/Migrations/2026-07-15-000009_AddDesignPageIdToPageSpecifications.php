<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class AddDesignPageIdToPageSpecifications extends Migration
{
    public function up()
    {
        $this->forge->addColumn(Tables::BLUEPRINT_PAGE_SPECIFICATIONS, [
            'design_page_id' => [
                'type'       => 'BIGINT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'module_id',
            ],
        ]);

        $specs = $this->db->table(Tables::BLUEPRINT_PAGE_SPECIFICATIONS)
            ->where('design_page_id IS NULL')
            ->get()
            ->getResultArray();

        foreach ($specs as $spec) {
            $firstPage = $this->db->table(Tables::BLUEPRINT_DESIGN_PAGES)
                ->where('module_id', $spec['module_id'])
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();

            if ($firstPage) {
                $this->db->table(Tables::BLUEPRINT_PAGE_SPECIFICATIONS)
                    ->where('id', $spec['id'])
                    ->update(['design_page_id' => $firstPage['id']]);
            }
        }

        $this->forge->modifyColumn(Tables::BLUEPRINT_PAGE_SPECIFICATIONS, [
            'design_page_id' => [
                'type'       => 'BIGINT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('design_page_id');
        $this->forge->addForeignKey('design_page_id', Tables::BLUEPRINT_DESIGN_PAGES, 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropColumn(Tables::BLUEPRINT_PAGE_SPECIFICATIONS, 'design_page_id');
    }
}
