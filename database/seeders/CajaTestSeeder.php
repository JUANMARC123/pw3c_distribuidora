<?php

namespace Database\Seeders;

use App\Models\AsignacionVehiculoRepartidor;
use App\Models\CajaRepartidor;
use App\Models\Farmacia;
use App\Models\GastoOperativo;
use App\Models\HojaRuta;
use App\Models\HojaRutaDetalle;
use App\Models\Pedido;
use App\Models\Repartidor;
use App\Models\Vehiculo;
use Illuminate\Database\Seeder;

class CajaTestSeeder extends Seeder
{
    public function run()
    {
        $repartidores = Repartidor::factory()->count(3)->create(['estado' => 'activo']);

        $vehiculos = Vehiculo::factory()->count(3)->create(['estado' => 'disponible']);

        foreach ($repartidores as $i => $repartidor) {
            AsignacionVehiculoRepartidor::factory()->create([
                'id_repartidor' => $repartidor->id_repartidor,
                'id_vehiculo' => $vehiculos[$i]->id_vehiculo,
            ]);
        }

        $farmacias = Farmacia::factory()->count(5)->create();

        $pedidos = collect();
        $estados = ['pendiente', 'entregado', 'cancelado'];
        for ($i = 0; $i < 15; $i++) {
            $pedidos->push(
                Pedido::factory()->{$estados[$i % 3]}()->create([
                    'id_farmacia' => $farmacias[$i % 5]->id_farmacia,
                ])
            );
        }

        $cajaAbierta = CajaRepartidor::factory()->create([
            'id_repartidor' => $repartidores[0]->id_repartidor,
            'estado_caja' => 'abierta',
            'monto_inicial' => 200,
            'ingresos' => 1500,
        ]);

        $cajaCerrada1 = CajaRepartidor::factory()->cerrada()->create([
            'id_repartidor' => $repartidores[1]->id_repartidor,
            'monto_inicial' => 100,
            'ingresos' => 800,
            'monto_final' => 900,
        ]);

        $cajaCerrada2 = CajaRepartidor::factory()->cerrada()->create([
            'id_repartidor' => $repartidores[2]->id_repartidor,
            'monto_inicial' => 150,
            'ingresos' => 1200,
            'monto_final' => 1350,
        ]);

        $hojaPlanificada = HojaRuta::factory()->create([
            'fecha' => now()->format('Y-m-d'),
            'id_repartidor' => $repartidores[0]->id_repartidor,
            'id_vehiculo' => $vehiculos[0]->id_vehiculo,
            'id_caja' => $cajaAbierta->id_caja,
            'estado_ruta' => 'planificado',
        ]);

        $hojaCompletada = HojaRuta::factory()->completado()->create([
            'fecha' => now()->subDay()->format('Y-m-d'),
            'id_repartidor' => $repartidores[0]->id_repartidor,
            'id_vehiculo' => $vehiculos[0]->id_vehiculo,
            'id_caja' => $cajaAbierta->id_caja,
            'cobrado_efectivo' => 1200,
            'cobrado_qr' => 300,
        ]);

        $hoja1Pedidos = $pedidos->slice(0, 3);
        $hoja2Pedidos = $pedidos->slice(3, 4);

        foreach ($hoja1Pedidos as $orden => $pedido) {
            HojaRutaDetalle::factory()->create([
                'id_hoja_ruta' => $hojaPlanificada->id_hoja_ruta,
                'id_pedido' => $pedido->id_pedido,
                'orden_visita' => $orden + 1,
            ]);
        }

        foreach ($hoja2Pedidos as $orden => $pedido) {
            HojaRutaDetalle::factory()->create([
                'id_hoja_ruta' => $hojaCompletada->id_hoja_ruta,
                'id_pedido' => $pedido->id_pedido,
                'orden_visita' => $orden + 1,
                'hora_llegada' => now()->subDay()->setTime(9 + $orden, 0)->format('H:i:s'),
                'hora_salida' => now()->subDay()->setTime(9 + $orden, 30 + $orden * 5)->format('H:i:s'),
            ]);
        }

        GastoOperativo::factory()->create([
            'id_caja' => $cajaAbierta->id_caja,
            'tipo_gasto' => 'COMBUSTIBLE',
            'descripcion' => 'Carga de combustible para ruta del día',
            'monto' => 150,
            'fecha' => now(),
        ]);
    }
}
