<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Tables;

class AddReferralToTickets extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames(Tables::TICKETS);
        if (!in_array('referral', $fields, true)) {
            $db->query("ALTER TABLE `" . Tables::TICKETS . "` ADD COLUMN `referral` VARCHAR(20) NULL AFTER `tracking_code`");
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames(Tables::TICKETS);
        if (in_array('referral', $fields, true)) {
            $db->query("ALTER TABLE `" . Tables::TICKETS . "` DROP COLUMN `referral`");
        }
    }
}
