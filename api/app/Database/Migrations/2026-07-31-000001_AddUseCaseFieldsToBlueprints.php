<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUseCaseFieldsToBlueprints extends Migration
{
    public function up()
    {
        $this->db()->query("ALTER TABLE `blueprints` ADD COLUMN `actors` TEXT NULL AFTER `description`");
        $this->db()->query("ALTER TABLE `blueprints` ADD COLUMN `pre_condition` TEXT NULL AFTER `actors`");
        $this->db()->query("ALTER TABLE `blueprints` ADD COLUMN `post_condition` TEXT NULL AFTER `pre_condition`");
        $this->db()->query("ALTER TABLE `blueprints` ADD COLUMN `normal_course` TEXT NULL AFTER `post_condition`");
        $this->db()->query("ALTER TABLE `blueprints` ADD COLUMN `exception` TEXT NULL AFTER `normal_course`");
        $this->db()->query("ALTER TABLE `blueprints` ADD COLUMN `frequency` VARCHAR(20) NULL AFTER `exception`");
        $this->db()->query("ALTER TABLE `blueprints` ADD COLUMN `notes` TEXT NULL AFTER `frequency`");
        $this->db()->query("ALTER TABLE `blueprints` ADD COLUMN `issue` TEXT NULL AFTER `notes`");
    }

    public function down()
    {
        $this->db()->query("ALTER TABLE `blueprints` DROP COLUMN `issue`");
        $this->db()->query("ALTER TABLE `blueprints` DROP COLUMN `notes`");
        $this->db()->query("ALTER TABLE `blueprints` DROP COLUMN `frequency`");
        $this->db()->query("ALTER TABLE `blueprints` DROP COLUMN `exception`");
        $this->db()->query("ALTER TABLE `blueprints` DROP COLUMN `normal_course`");
        $this->db()->query("ALTER TABLE `blueprints` DROP COLUMN `post_condition`");
        $this->db()->query("ALTER TABLE `blueprints` DROP COLUMN `pre_condition`");
        $this->db()->query("ALTER TABLE `blueprints` DROP COLUMN `actors`");
    }
}
