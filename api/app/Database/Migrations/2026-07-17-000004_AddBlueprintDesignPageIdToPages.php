<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBlueprintDesignPageIdToPages extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $db->query('ALTER TABLE `pages` ADD `blueprint_design_page_id` BIGINT UNSIGNED NULL AFTER `module_id`');

        $db->query('ALTER TABLE `pages` ADD CONSTRAINT `pages_blueprint_design_page_id_foreign` FOREIGN KEY (`blueprint_design_page_id`) REFERENCES `blueprint_design_pages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $db->query('ALTER TABLE `pages` DROP FOREIGN KEY `pages_blueprint_design_page_id_foreign`');

        $db->query('ALTER TABLE `pages` DROP COLUMN `blueprint_design_page_id`');
    }
}
