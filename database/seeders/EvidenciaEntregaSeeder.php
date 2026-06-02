<?php

namespace Database\Seeders;

use App\Models\EvidenciaEntrega;
use Illuminate\Database\Seeder;

class EvidenciaEntregaSeeder extends Seeder
{
    public function run()
    {
        EvidenciaEntrega::factory()->count(10)->create();
    }
}
