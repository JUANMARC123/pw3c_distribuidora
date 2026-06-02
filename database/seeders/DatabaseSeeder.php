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
            UsuarioRolSeeder::class,
            RolPermisoSeeder::class,
            AuditoriaSeeder::class,
            SesionUsuarioSeeder::class,
            AccesoSistemaSeeder::class,
            RepartidorSeeder::class,
            FarmaciaSeeder::class,
            ProductoSeeder::class,
            GeocercaSeeder::class,
            VehiculoSeeder::class,
            ContactoFarmaciaSeeder::class,
            ProductoPrincipioSeeder::class,
            PresentacionSeeder::class,
            LoteSeeder::class,
            RutaParadaSeeder::class,
            UbicacionGpsSeeder::class,
            InventarioSeeder::class,
            DespachoSeeder::class,
            DetalleDespachoSeeder::class,
            EvidenciaEntregaSeeder::class,
            IncidenciaSeeder::class,
            MovimientoInventarioSeeder::class,
        ]);
    }
}
