<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class CreateAccessModules extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'       => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'slug'     => ['type' => 'VARCHAR', 'constraint' => 50],
            'icon'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'sort_order' => ['type' => 'INT', 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable(Tables::ACCESS_MODULES);
    }

    public function down()
    {
        $this->forge->dropTable(Tables::ACCESS_MODULES);
    }
}
