<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDesignPageIdToPageSpecifications extends Migration
{
    public function up()
    {
        $this->forge->addColumn('blueprint_page_specifications', [
            'design_page_id' => [
                'type'       => 'BIGINT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'module_id',
            ],
        ]);

        $specs = $this->db->table('blueprint_page_specifications')
            ->where('design_page_id IS NULL')
            ->get()
            ->getResultArray();

        foreach ($specs as $spec) {
            $firstPage = $this->db->table('blueprint_design_pages')
                ->where('module_id', $spec['module_id'])
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getRowArray();

            if ($firstPage) {
                $this->db->table('blueprint_page_specifications')
                    ->where('id', $spec['id'])
                    ->update(['design_page_id' => $firstPage['id']]);
            }
        }

        $this->forge->modifyColumn('blueprint_page_specifications', [
            'design_page_id' => [
                'type'       => 'BIGINT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('design_page_id');
        $this->forge->addForeignKey('design_page_id', 'blueprint_design_pages', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropColumn('blueprint_page_specifications', 'design_page_id');
    }
}
