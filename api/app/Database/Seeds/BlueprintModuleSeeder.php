<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Config\Tables;

class BlueprintModuleSeeder extends Seeder
{
    public function run()
    {
        $this->db->table(Tables::ACCESS_MODULES)->insert([
            'name'       => 'Blueprints',
            'slug'       => 'blueprints',
            'icon'       => 'fas fa-drafting-compass',
            'sort_order' => 8,
        ]);
    }
}
