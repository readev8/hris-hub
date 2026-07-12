<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

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
        $this->forge->createTable('access_modules');
    }

    public function down()
    {
        $this->forge->dropTable('access_modules');
    }
}
