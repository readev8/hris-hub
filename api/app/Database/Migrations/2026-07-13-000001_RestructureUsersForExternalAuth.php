<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class RestructureUsersForExternalAuth extends Migration
{
    public function up()
    {
        // Add user_id column (references myhr/plus userId)
        $this->forge->addColumn(Tables::USERS, [
            'user_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'id'],
        ]);
        $this->forge->addUniqueKey('user_id');

        // Drop password column (auth is external via myhr/plus)
        $this->forge->dropColumn(Tables::USERS, 'password');

        // Add approver_id to projects table (for per-user approval)
        $this->forge->addColumn(Tables::PROJECTS, [
            'approver_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'assignee_id'],
        ]);
        $this->forge->addForeignKey('approver_id', Tables::USERS, 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropColumn(Tables::USERS, 'user_id');

        $this->forge->addColumn(Tables::USERS, [
            'password' => ['type' => 'VARCHAR', 'constraint' => 255, 'after' => 'email'],
        ]);

        $this->forge->dropColumn(Tables::PROJECTS, 'approver_id');
    }
}
