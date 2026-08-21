<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class AddLabelToFlowNodePositions extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (!$db->fieldExists('label', Tables::FLOW_NODE_POSITIONS)) {
            $this->forge->addColumn(Tables::FLOW_NODE_POSITIONS, [
                'label' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'module_id'],
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if ($db->fieldExists('label', Tables::FLOW_NODE_POSITIONS)) {
            $this->forge->dropColumn(Tables::FLOW_NODE_POSITIONS, ['label']);
        }
    }
}
