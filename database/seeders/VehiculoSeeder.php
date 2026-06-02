<?php

namespace Database\Seeders;

use App\Models\Vehiculo;
use Illuminate\Database\Seeder;

class VehiculoSeeder extends Seeder
{
    public function run()
    {
        Vehiculo::factory()->count(10)->create();
    }
}
