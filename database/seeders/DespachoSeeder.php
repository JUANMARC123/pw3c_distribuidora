<?php

namespace Database\Seeders;

use App\Models\Despacho;
use Illuminate\Database\Seeder;

class DespachoSeeder extends Seeder
{
    public function run()
    {
        Despacho::factory()->count(10)->create();
    }
}
