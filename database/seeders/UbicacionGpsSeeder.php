<?php

namespace Database\Seeders;

use App\Models\UbicacionGps;
use Illuminate\Database\Seeder;

class UbicacionGpsSeeder extends Seeder
{
    public function run()
    {
        UbicacionGps::factory()->count(10)->create();
    }
}
