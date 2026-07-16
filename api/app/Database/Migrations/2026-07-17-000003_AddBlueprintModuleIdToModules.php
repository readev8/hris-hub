<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBlueprintModuleIdToModules extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $db->query('ALTER TABLE `modules` ADD `blueprint_module_id` BIGINT UNSIGNED NULL AFTER `sort_order`');

        $db->query('ALTER TABLE `modules` ADD CONSTRAINT `modules_blueprint_module_id_foreign` FOREIGN KEY (`blueprint_module_id`) REFERENCES `blueprint_modules` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $db->query('ALTER TABLE `modules` DROP FOREIGN KEY `modules_blueprint_module_id_foreign`');

        $db->query('ALTER TABLE `modules` DROP COLUMN `blueprint_module_id`');
    }
}
