<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            CatalogoSeeder::class,
            DemoDataSeeder::class,
            RepartidorSeeder::class,
            VehiculoSeeder::class,
            FarmaciaSeeder::class,
            ProductoSeeder::class,
            PedidoSeeder::class,
        ]);
    }
}
