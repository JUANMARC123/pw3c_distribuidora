<?php

namespace Database\Seeders;

use App\Models\Incidencia;
use Illuminate\Database\Seeder;

class IncidenciaSeeder extends Seeder
{
    public function run()
    {
        Incidencia::factory()->count(10)->create();
    }
}
