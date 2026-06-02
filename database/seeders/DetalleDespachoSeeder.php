<?php

namespace Database\Seeders;

use App\Models\DetalleDespacho;
use Illuminate\Database\Seeder;

class DetalleDespachoSeeder extends Seeder
{
    public function run()
    {
        DetalleDespacho::factory()->count(10)->create();
    }
}
