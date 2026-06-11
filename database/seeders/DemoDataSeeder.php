<?php

namespace Database\Seeders;

use App\Models\Seguridad\Usuario;
use App\Models\Seguridad\Rol;
use App\Models\Farmacia\Farmacia;
use App\Models\Farmacia\ContactoFarmacia;
use App\Models\Repartidor\Repartidor;
use App\Models\Vehiculo\Vehiculo;
use App\Models\Logistica\Ruta;
use App\Models\Logistica\RutaParada;
use App\Models\Pedido\Pedido;
use App\Models\Logistica\ControlRuta;
use App\Models\Despacho\Despacho;
use App\Models\Pedido\HistorialEstadoPedido;
use App\Models\Repartidor\HistorialEstadoRepartidor;
use App\Models\Vehiculo\HistorialEstadoVehiculo;
use App\Models\Despacho\HistorialEstadoDespacho;
use App\Models\Evidencia\Incidencia;
use App\Models\Evidencia\EvidenciaEntrega;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        $usuarios = Usuario::factory(10)->create();
        $usuarios->first()->update(['email' => 'admin@distribuidora.com']);

        foreach ($usuarios as $index => $usuario) {
            $rolId = ($index === 0) ? 1 : fake()->numberBetween(2, 4);
            $usuario->roles()->attach($rolId);
        }

        $farmacias = Farmacia::factory(10)->create();

        foreach ($farmacias as $farmacia) {
            ContactoFarmacia::factory(1)->create([
                'id_farmacia' => $farmacia->id_farmacia,
                'id_cargo' => fake()->numberBetween(1, 5),
            ]);
        }

        foreach ($usuarios->take(5) as $usuario) {
            Repartidor::factory(1)->create(['id_usuario' => $usuario->id_usuario]);
        }

        Vehiculo::factory(10)->create();

        $rutas = Ruta::factory(5)->create();

        foreach ($rutas as $ruta) {
            $farmaciasSeleccionadas = $farmacias->random(3);
            $orden = 1;
            foreach ($farmaciasSeleccionadas as $farmacia) {
                RutaParada::factory(1)->create([
                    'id_ruta' => $ruta->id_ruta,
                    'id_farmacia' => $farmacia->id_farmacia,
                    'orden_parada' => $orden++,
                    'hora_estimada' => sprintf('%02d:%02d:00', 7 + $orden, 0),
                ]);
            }
        }

        foreach ($rutas as $ruta) {
            for ($i = 0; $i < 2; $i++) {
                ControlRuta::factory(1)->create([
                    'id_ruta' => $ruta->id_ruta,
                    'id_repartidor' => fake()->numberBetween(1, 5),
                    'id_vehiculo' => fake()->numberBetween(1, 10),
                ]);
            }
        }

        foreach ($farmacias as $farmacia) {
            Pedido::factory(1)->create([
                'id_farmacia' => $farmacia->id_farmacia,
                'id_usuario' => $usuarios->random()->id_usuario,
                'id_estado_pedido' => fake()->numberBetween(1, 5),
            ]);
        }

        $pedidos = Pedido::all();
        foreach ($pedidos as $pedido) {
            HistorialEstadoPedido::create([
                'id_pedido' => $pedido->id_pedido,
                'id_estado_pedido' => $pedido->id_estado_pedido,
                'fecha_inicio' => $pedido->fecha_pedido,
            ]);

            if (in_array($pedido->id_estado_pedido, [4, 5])) {
                $parada = RutaParada::where('id_farmacia', $pedido->id_farmacia)->first();
                $controlRuta = ControlRuta::where('id_ruta', $parada?->id_ruta)->first();

                if ($parada && $controlRuta) {
                    Despacho::factory(1)->create([
                        'id_pedido' => $pedido->id_pedido,
                        'id_parada' => $parada->id_parada,
                        'id_control_ruta' => $controlRuta->id_control_ruta,
                        'id_estado_despacho' => fake()->numberBetween(1, 4),
                    ]);
                }
            }
        }

        $despachos = Despacho::all();
        foreach ($despachos as $despacho) {
            HistorialEstadoDespacho::create([
                'id_despacho' => $despacho->id_despacho,
                'id_estado_despacho' => $despacho->id_estado_despacho,
                'fecha_inicio' => $despacho->fecha_hora_despacho,
            ]);

            if (fake()->boolean(40)) {
                Incidencia::create([
                    'id_despacho' => $despacho->id_despacho,
                    'id_tipo_incidencia' => fake()->numberBetween(1, 6),
                    'descripcion' => fake()->sentence(),
                    'fecha_incidencia' => now(),
                ]);
            }

            EvidenciaEntrega::create([
                'id_despacho' => $despacho->id_despacho,
                'id_tipo_evidencia' => fake()->numberBetween(1, 3),
                'archivo' => 'evidencias/' . fake()->uuid() . '.jpg',
                'fecha_registro' => now(),
            ]);
        }

        $repartidores = Repartidor::all();
        foreach ($repartidores as $repartidor) {
            HistorialEstadoRepartidor::create([
                'id_repartidor' => $repartidor->id_repartidor,
                'id_estado_repartidor' => $repartidor->id_estado_repartidor,
                'fecha_inicio' => now()->subDays(fake()->numberBetween(1, 30)),
            ]);
        }

        $vehiculos = Vehiculo::all();
        foreach ($vehiculos as $vehiculo) {
            HistorialEstadoVehiculo::create([
                'id_vehiculo' => $vehiculo->id_vehiculo,
                'id_estado_vehiculo' => $vehiculo->id_estado_vehiculo,
                'fecha_inicio' => now()->subDays(fake()->numberBetween(1, 30)),
            ]);
        }
    }
}
