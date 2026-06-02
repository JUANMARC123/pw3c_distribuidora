<?php

namespace Database\Seeders;

use App\Models\Presentacion;
use Illuminate\Database\Seeder;

class PresentacionSeeder extends Seeder
{
    public function run()
    {
        Presentacion::factory()->count(10)->create();
    }
}
