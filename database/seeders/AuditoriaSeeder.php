<?php

namespace Database\Seeders;

use App\Models\Auditoria;
use Illuminate\Database\Seeder;

class AuditoriaSeeder extends Seeder
{
    public function run()
    {
        Auditoria::factory()->count(10)->create();
    }
}
