<?php

namespace Database\Seeders;

use App\Models\Farmacia;
use Illuminate\Database\Seeder;

class FarmaciaSeeder extends Seeder
{
    public function run()
    {
        Farmacia::factory()->count(10)->create();
    }
}
