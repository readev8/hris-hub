<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReferralToTickets extends Migration
{
    public function up()
    {
        $this->db()->query("ALTER TABLE `tickets` ADD COLUMN `referral` VARCHAR(20) NULL AFTER `tracking_code`");
    }

    public function down()
    {
        $this->db()->query("ALTER TABLE `tickets` DROP COLUMN `referral`");
    }
}
