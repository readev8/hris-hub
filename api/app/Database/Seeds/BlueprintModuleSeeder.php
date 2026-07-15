<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BlueprintModuleSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('access_modules')->insert([
            'name'       => 'Blueprints',
            'slug'       => 'blueprints',
            'icon'       => 'fas fa-drafting-compass',
            'sort_order' => 8,
        ]);
    }
}
