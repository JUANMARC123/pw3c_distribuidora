<?php

namespace Database\Seeders;

use App\Models\Geocerca;
use Illuminate\Database\Seeder;

class GeocercaSeeder extends Seeder
{
    public function run()
    {
        Geocerca::factory()->count(10)->create();
    }
}
