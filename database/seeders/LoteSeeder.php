<?php

namespace Database\Seeders;

use App\Models\Lote;
use Illuminate\Database\Seeder;

class LoteSeeder extends Seeder
{
    public function run()
    {
        Lote::factory()->count(10)->create();
    }
}
