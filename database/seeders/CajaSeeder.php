<?php

namespace Database\Seeders;

use App\Models\AsignacionVehiculoRepartidor;
use App\Models\CajaIngreso;
use App\Models\CajaRepartidor;
use App\Models\Despacho;
use App\Models\EvidenciaEntrega;
use App\Models\Farmacia;
use App\Models\GastoOperativo;
use App\Models\HojaRuta;
use App\Models\HojaRutaDetalle;
use App\Models\Pedido;
use App\Models\Repartidor;
use App\Models\Usuario;
use App\Models\Vehiculo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CajaSeeder extends Seeder
{
    protected $faker;

    private const PNG_EVIDENCIA = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    public function run()
    {
        $meses = (int) (env('CAJA_SEEDER_MESES', 1));

        $repartidores = Repartidor::factory()->count(3)->create(['estado' => 'activo']);
        $vehiculos = Vehiculo::factory()->count(3)->create(['estado' => 'disponible']);

        foreach ($repartidores as $i => $repartidor) {
            AsignacionVehiculoRepartidor::factory()->create([
                'id_repartidor' => $repartidor->id_repartidor,
                'id_vehiculo' => $vehiculos[$i]->id_vehiculo,
            ]);
        }

        $farmacias = Farmacia::all();

        if ($farmacias->count() < 8) {
            $farmacias = Farmacia::factory()->count(8)->create();
        }

        $usuarioVentas = Usuario::factory()->create([
            'nombre' => 'Ventas',
            'apellido' => 'Sistema',
            'email' => 'ventas@caja.local',
        ]);

        $hoy = Carbon::today();
        $inicio = $hoy->copy()->subMonths($meses)->startOfDay();
        $dia = $inicio->copy();

        $this->command->info("Generando cajas desde {$inicio->toDateString()} hasta {$hoy->toDateString()}...");

        $totalDias = $inicio->diffInDays($hoy) + 1;
        $bar = $this->command->getOutput()->createProgressBar($totalDias);
        $bar->start();

        \Illuminate\Support\Facades\DB::transaction(function () use ($repartidores, $vehiculos, $farmacias, $usuarioVentas, $hoy, $inicio, $bar) {
            $dia = $inicio->copy();

            while ($dia->lte($hoy)) {
            $fecha = $dia->toDateString();
            $esHoy = $dia->isSameDay($hoy);

            if ($dia->isSunday()) {
                $bar->advance();
                $dia->addDay();
                continue;
            }

            foreach ($repartidores as $idx => $repartidor) {
                if (!$esHoy && $this->sinCaja()) {
                    continue;
                }

                if ($esHoy && $idx !== 0) {
                    continue;
                }

                $estado = $esHoy ? 'abierta' : $this->estadoHistorico();
                $montoInicial = $this->faker()->randomFloat(2, 100, 500);
                $cobradoEfectivo = 0;
                $cobradoQr = 0;

                $caja = CajaRepartidor::create([
                    'id_repartidor' => $repartidor->id_repartidor,
                    'fecha' => $fecha,
                    'monto_inicial' => $montoInicial,
                    'ingresos' => 0,
                    'monto_final' => $esHoy ? null : 0,
                    'estado_caja' => $estado,
                ]);

                $estadoRuta = $esHoy ? 'planificado' : 'completado';
                $hoja = HojaRuta::factory()->create([
                    'fecha' => $fecha,
                    'id_repartidor' => $repartidor->id_repartidor,
                    'id_vehiculo' => $vehiculos[$idx]->id_vehiculo,
                    'id_caja' => $caja->id_caja,
                    'cobrado_efectivo' => $cobradoEfectivo,
                    'cobrado_qr' => $cobradoQr,
                    'estado_ruta' => $estadoRuta,
                ]);

                $numPedidos = $esHoy ? 0 : rand(6, 8);
                $farmaciasDisponibles = $farmacias->shuffle();
                $totalIngresosDetalle = 0;

                for ($p = 0; $p < $numPedidos; $p++) {
                    $farmacia = $farmaciasDisponibles[$p % $farmaciasDisponibles->count()];

                    $pedido = Pedido::factory()->entregado()->create([
                        'id_farmacia' => $farmacia->id_farmacia,
                        'id_usuario' => $usuarioVentas->id_usuario,
                        'fecha_pedido' => $dia->copy()->setTime(rand(8, 17), rand(0, 59)),
                    ]);

                    $detalle = HojaRutaDetalle::factory()->create([
                        'id_hoja_ruta' => $hoja->id_hoja_ruta,
                        'id_pedido' => $pedido->id_pedido,
                        'orden_visita' => $p + 1,
                        'hora_llegada' => $dia->copy()->setTime(8 + $p, 15 * $p)->format('H:i:s'),
                        'hora_salida' => $dia->copy()->setTime(8 + $p, 30 + 15 * $p)->format('H:i:s'),
                    ]);

                    $estadoDespacho = $this->faker()->boolean(5) ? 'incidencia' : 'entregado';
                    $metodoPago = $this->faker()->randomElement(['efectivo', 'qr', 'transferencia', 'credito']);
                    $montoDespacho = (float) $pedido->total;

                    $despacho = Despacho::create([
                        'id_pedido' => $pedido->id_pedido,
                        'id_detalle_hoja_ruta' => $detalle->id_detalle,
                        'fecha_hora_despacho' => $dia->copy()->setTime(8 + $p, 30 + 15 * $p),
                        'estado_despacho' => $estadoDespacho,
                        'monto_recibido' => $estadoDespacho === 'entregado' ? $montoDespacho : null,
                        'metodo_pago' => $estadoDespacho === 'entregado' ? $metodoPago : null,
                    ]);

                    if ($estadoDespacho === 'incidencia') {
                        $pedido->update(['estado' => 'cancelado']);
                    }

                    if ($estadoDespacho === 'entregado' && $metodoPago === 'efectivo') {
                        $cobradoEfectivo += $montoDespacho;
                    } elseif ($estadoDespacho === 'entregado' && $metodoPago === 'qr') {
                        $cobradoQr += $montoDespacho;
                    }

                    if ($estadoDespacho === 'entregado') {
                        $numEvidencias = rand(1, 2);

                        for ($e = 0; $e < $numEvidencias; $e++) {
                            $archivo = 'evidencias/' . Str::uuid() . '.png';
                            Storage::disk('public')->put($archivo, base64_decode(self::PNG_EVIDENCIA));

                            EvidenciaEntrega::create([
                                'id_despacho' => $despacho->id_despacho,
                                'tipo_evidencia' => $this->faker()->randomElement(['firma', 'foto', 'sello']),
                                'archivo' => $archivo,
                            ]);
                        }
                    }
                }

                if (!$esHoy && $numPedidos > 0) {
                    $hoja->update([
                        'cobrado_efectivo' => $cobradoEfectivo,
                        'cobrado_qr' => $cobradoQr,
                    ]);
                }

                $totalGastos = 0;
                $numGastos = $esHoy ? 0 : rand(1, 3);
                for ($g = 0; $g < $numGastos; $g++) {
                    $gastoMonto = $this->faker()->randomFloat(2, 10, 300);
                    $totalGastos += $gastoMonto;
                    GastoOperativo::factory()->create([
                        'id_caja' => $caja->id_caja,
                        'monto' => $gastoMonto,
                        'fecha' => $dia->copy()->setTime(rand(8, 18), rand(0, 59)),
                    ]);
                }

                $numIngresos = $esHoy ? 0 : rand(0, 2);
                for ($i = 0; $i < $numIngresos; $i++) {
                    $montoIngreso = $this->faker()->randomFloat(2, 50, 500);
                    $totalIngresosDetalle += $montoIngreso;
                    CajaIngreso::create([
                        'id_caja' => $caja->id_caja,
                        'id_hoja_ruta' => $this->faker()->boolean(70) ? $hoja->id_hoja_ruta : null,
                        'descripcion' => $this->faker()->randomElement([
                            'Cobro transferencia bancaria',
                            'Ajuste de vuelto',
                            'Cobro de pedido urgente',
                            'Liquidacion parcial de ruta',
                        ]),
                        'monto' => $montoIngreso,
                    ]);
                }

                $caja->update([
                    'ingresos' => $totalIngresosDetalle,
                    'monto_final' => $esHoy
                        ? null
                        : round($montoInicial + $totalIngresosDetalle + $cobradoEfectivo - $totalGastos, 2),
                ]);
            }

            $bar->advance();
                $dia->addDay();
            }
        });

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info('Cajas generadas correctamente.');
    }

    protected function estadoHistorico()
    {
        return $this->faker()->boolean(70) ? 'cerrada' : 'auditada';
    }

    protected function sinCaja()
    {
        return $this->faker()->boolean(15);
    }

    protected function faker()
    {
        if (!$this->faker) {
            $this->faker = \Faker\Factory::create('es_ES');
        }
        return $this->faker;
    }
}
