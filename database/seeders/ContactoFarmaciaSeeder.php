<?php

namespace Database\Seeders;

use App\Models\ContactoFarmacia;
use Illuminate\Database\Seeder;

class ContactoFarmaciaSeeder extends Seeder
{
    public function run()
    {
        ContactoFarmacia::factory()->count(10)->create();
    }
}
