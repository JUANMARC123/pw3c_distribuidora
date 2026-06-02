<?php

namespace Database\Seeders;

use App\Models\SesionUsuario;
use Illuminate\Database\Seeder;

class SesionUsuarioSeeder extends Seeder
{
    public function run()
    {
        SesionUsuario::factory()->count(10)->create();
    }
}
