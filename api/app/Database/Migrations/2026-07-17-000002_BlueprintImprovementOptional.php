<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class BlueprintImprovementOptional extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $db->query('ALTER TABLE `' . Tables::BLUEPRINTS . '` DROP FOREIGN KEY `blueprints_improvement_id_foreign`');

        $db->query('ALTER TABLE `' . Tables::BLUEPRINTS . '` DROP INDEX `improvement_id`');

        $db->query('ALTER TABLE `' . Tables::BLUEPRINTS . '` ADD INDEX `improvement_id` (`improvement_id`)');

        $db->query('ALTER TABLE `' . Tables::BLUEPRINTS . '` MODIFY `improvement_id` BIGINT UNSIGNED NULL');

        $db->query('ALTER TABLE `' . Tables::BLUEPRINTS . '` ADD CONSTRAINT `blueprints_improvement_id_foreign` FOREIGN KEY (`improvement_id`) REFERENCES `' . Tables::PROJECTS . '` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $db->query('ALTER TABLE `' . Tables::BLUEPRINTS . '` DROP FOREIGN KEY `blueprints_improvement_id_foreign`');

        $db->query('ALTER TABLE `' . Tables::BLUEPRINTS . '` DROP INDEX `improvement_id`');

        $db->query('ALTER TABLE `' . Tables::BLUEPRINTS . '` MODIFY `improvement_id` BIGINT UNSIGNED NOT NULL');

        $db->query('ALTER TABLE `' . Tables::BLUEPRINTS . '` ADD UNIQUE INDEX `improvement_id` (`improvement_id`)');

        $db->query('ALTER TABLE `' . Tables::BLUEPRINTS . '` ADD CONSTRAINT `blueprints_improvement_id_foreign` FOREIGN KEY (`improvement_id`) REFERENCES `' . Tables::PROJECTS . '` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
    }
}
