<?php

namespace Database\Seeders;

use App\Models\PrincipioActivo;
use Illuminate\Database\Seeder;

class PrincipioActivoSeeder extends Seeder
{
    public function run()
    {
        PrincipioActivo::factory()->count(10)->create();
    }
}
