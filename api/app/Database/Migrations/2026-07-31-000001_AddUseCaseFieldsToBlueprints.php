<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class AddUseCaseFieldsToBlueprints extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $columns = [
            'actors'         => "ADD COLUMN `actors` TEXT NULL AFTER `description`",
            'pre_condition'  => "ADD COLUMN `pre_condition` TEXT NULL AFTER `actors`",
            'post_condition' => "ADD COLUMN `post_condition` TEXT NULL AFTER `pre_condition`",
            'normal_course'  => "ADD COLUMN `normal_course` TEXT NULL AFTER `post_condition`",
            'exception'      => "ADD COLUMN `exception` TEXT NULL AFTER `normal_course`",
            'frequency'      => "ADD COLUMN `frequency` VARCHAR(20) NULL AFTER `exception`",
            'notes'          => "ADD COLUMN `notes` TEXT NULL AFTER `frequency`",
            'issue'          => "ADD COLUMN `issue` TEXT NULL AFTER `notes`",
        ];

        $fields = $db->getFieldNames(Tables::BLUEPRINTS);
        foreach ($columns as $col => $sql) {
            if (!in_array($col, $fields, true)) {
                $db->query("ALTER TABLE `" . Tables::BLUEPRINTS . "` " . $sql);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $columns = ['issue', 'notes', 'frequency', 'exception', 'normal_course', 'post_condition', 'pre_condition', 'actors'];

        $fields = $db->getFieldNames(Tables::BLUEPRINTS);
        foreach ($columns as $col) {
            if (in_array($col, $fields, true)) {
                $db->query("ALTER TABLE `" . Tables::BLUEPRINTS . "` DROP COLUMN `" . $col . "`");
            }
        }
    }
}
