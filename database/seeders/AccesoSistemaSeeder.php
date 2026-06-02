<?php

namespace Database\Seeders;

use App\Models\AccesoSistema;
use Illuminate\Database\Seeder;

class AccesoSistemaSeeder extends Seeder
{
    public function run()
    {
        AccesoSistema::factory()->count(10)->create();
    }
}
