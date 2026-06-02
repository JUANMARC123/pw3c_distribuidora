<?php

namespace Database\Seeders;

use App\Models\Laboratorio;
use Illuminate\Database\Seeder;

class LaboratorioSeeder extends Seeder
{
    public function run()
    {
        Laboratorio::factory()->count(10)->create();
    }
}
