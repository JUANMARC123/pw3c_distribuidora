<?php

namespace Database\Seeders;

use App\Models\Repartidor;
use Illuminate\Database\Seeder;

class RepartidorSeeder extends Seeder
{
    public function run()
    {
        Repartidor::factory()->count(4)->create(['estado' => 'activo']);

        Repartidor::factory()->count(2)->create([
            'estado' => 'inactivo',
            'categoria_licencia' => 'A',
        ]);

        Repartidor::factory()->count(2)->create([
            'estado' => 'en_ruta',
            'categoria_licencia' => 'C',
        ]);
    }
}
