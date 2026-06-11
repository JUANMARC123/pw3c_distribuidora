<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            RolSeeder::class,
            PermisoSeeder::class,
            CategoriaSeeder::class,
            LaboratorioSeeder::class,
            PrincipioActivoSeeder::class,
            FormaFarmaceuticaSeeder::class,
            RutaSeeder::class,
            RepartidorSeeder::class,
            FarmaciaSeeder::class,
            ProductoSeeder::class,
            VehiculoSeeder::class,
            LoteSeeder::class,
            InventarioSeeder::class,
            DespachoSeeder::class,
        ]);
    }
}
