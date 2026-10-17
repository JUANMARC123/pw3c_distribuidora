<?php

namespace Database\Seeders;

use App\Models\Vehiculo;
use Illuminate\Database\Seeder;

class VehiculoSeeder extends Seeder
{
    public function run()
    {
        Vehiculo::factory()->count(4)->create();

        Vehiculo::factory()->count(2)->taller()->create();

        Vehiculo::factory()->count(2)->enRuta()->create();
    }
}
