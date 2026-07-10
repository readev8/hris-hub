<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApprovalRequests extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'project_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'requester_id'   => ['type' => 'BIGINT', 'unsigned' => true],
            'approver_id'    => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'stage_sequence' => ['type' => 'INT'],
            'status'         => ['type' => 'TINYINT', 'default' => 0],
            'notes'          => ['type' => 'TEXT', 'null' => true],
            'reviewed_at'    => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'created_at'     => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['project_id']);
        $this->forge->addKey(['approver_id']);
        $this->forge->addKey(['requester_id']);
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('approver_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('requester_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('approval_requests');
    }

    public function down()
    {
        $this->forge->dropTable('approval_requests');
    }
}
