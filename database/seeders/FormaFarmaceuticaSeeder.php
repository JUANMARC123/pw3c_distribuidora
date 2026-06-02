<?php

namespace Database\Seeders;

use App\Models\FormaFarmaceutica;
use Illuminate\Database\Seeder;

class FormaFarmaceuticaSeeder extends Seeder
{
    public function run()
    {
        FormaFarmaceutica::factory()->count(10)->create();
    }
}
