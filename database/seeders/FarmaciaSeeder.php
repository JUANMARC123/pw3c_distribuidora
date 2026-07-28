<?php

namespace Database\Seeders;

use App\Models\ContactoFarmacia;
use App\Models\Farmacia;
use Illuminate\Database\Seeder;

class FarmaciaSeeder extends Seeder
{
    public function run()
    {
        $nombres = [
            'Farmacia Boliviana',
            'Farmacia San Martín',
            'Farmacia Central',
            'Farmacia del Pueblo',
            'Farmacia Santa Cecilia',
            'Farmacia San José',
            'Farmacia La Paz',
            'Farmacia Cochabamba',
            'Farmacia Santa Cruz',
            'Farmacia del Carmen',
            'Farmacia San Rafael',
            'Farmacia San Miguel',
        ];

        foreach ($nombres as $nombre) {
            $farmacia = Farmacia::factory()->create([
                'nombre' => $nombre,
            ]);

            ContactoFarmacia::factory()->count(rand(1, 2))->create([
                'id_farmacia' => $farmacia->id_farmacia,
            ]);
        }
    }
}
