<?php

namespace Database\Seeders;

use App\Models\Ruta;
use Illuminate\Database\Seeder;

class RutaSeeder extends Seeder
{
    public function run()
    {
        Ruta::factory()->count(10)->create();
    }
}
