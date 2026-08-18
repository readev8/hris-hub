<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class AddOperationalFieldsToProjects extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames(Tables::PROJECTS);

        $newFields = [
            'frekuensi_penggunaan' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'target_date'],
            'situasi_terkini'      => ['type' => 'TEXT', 'null' => true, 'after' => 'frekuensi_penggunaan'],
            'ada_data_dianalisa'   => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 0, 'after' => 'situasi_terkini'],
            'jenis_data_analisa'   => ['type' => 'TEXT', 'null' => true, 'after' => 'ada_data_dianalisa'],
            'tujuan_analisa'       => ['type' => 'TEXT', 'null' => true, 'after' => 'jenis_data_analisa'],
            'dampak_manfaat'       => ['type' => 'TEXT', 'null' => true, 'after' => 'tujuan_analisa'],
        ];

        $toAdd = array_diff_key($newFields, array_flip($fields));
        if (!empty($toAdd)) {
            $this->forge->addColumn(Tables::PROJECTS, $toAdd);
        }
    }

    public function down()
    {
        $this->forge->dropColumn(Tables::PROJECTS, [
            'frekuensi_penggunaan',
            'situasi_terkini',
            'ada_data_dianalisa',
            'jenis_data_analisa',
            'tujuan_analisa',
            'dampak_manfaat',
        ]);
    }
}
