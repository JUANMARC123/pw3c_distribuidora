<?php

namespace Database\Seeders;

use App\Models\MovimientoInventario;
use Illuminate\Database\Seeder;

class MovimientoInventarioSeeder extends Seeder
{
    public function run()
    {
        MovimientoInventario::factory()->count(10)->create();
    }
}
