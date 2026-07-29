<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMasterUserTypes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'description' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'active'      => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 1],
            'created_at'  => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
            'updated_at'  => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('active');
        $this->forge->createTable('master_user_types');

        $this->db->query("ALTER TABLE `master_user_types` ADD UNIQUE KEY `uq_master_user_types_name` (`name`)");

        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'project_id'   => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'user_type_id' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
            'created_at'   => ['type' => 'TIMESTAMP', 'null' => true, 'default' => null],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('project_user_types');

        $this->db->query("ALTER TABLE `project_user_types` ADD UNIQUE KEY `uq_put_project_type` (`project_id`, `user_type_id`)");

        $this->db->query("ALTER TABLE `project_user_types` ADD CONSTRAINT `fk_put_project` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");
        $this->db->query("ALTER TABLE `project_user_types` ADD CONSTRAINT `fk_put_user_type` FOREIGN KEY (`user_type_id`) REFERENCES `master_user_types`(`id`) ON DELETE CASCADE ON UPDATE CASCADE");

        $now = date('Y-m-d H:i:s');
        $this->db->table('master_user_types')->insertBatch([
            ['name' => 'Admin Departemen', 'description' => 'Administrator departemen yang mengelola sistem', 'active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kepala Departemen', 'description' => 'Kepala departemen yang melakukan approval', 'active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'User (Sistem)', 'description' => 'Pengguna sistem yang menggunakan aplikasi', 'active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `project_user_types` DROP FOREIGN KEY `fk_put_project`");
        $this->db->query("ALTER TABLE `project_user_types` DROP FOREIGN KEY `fk_put_user_type`");
        $this->forge->dropTable('project_user_types');
        $this->forge->dropTable('master_user_types');
    }
}
