<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\ApiController;
use App\Models\Despacho\Despacho;
use App\Models\Logistica\ControlRuta;
use App\Models\Logistica\ControlRutaParada;
use App\Models\Logistica\HistorialEstadoRuta;
use App\Models\Logistica\PlanificacionRuta;
use App\Models\Logistica\Ruta;
use App\Models\Logistica\RutaParada;
use App\Models\Pedido\Pedido;
use App\Models\Repartidor\Repartidor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CentroLogisticoController extends ApiController
{
    public function pedidosPendientes()
    {
        $pedidos = Pedido::with([
            'farmacia',
            'detalles.producto',
            'detalles.estado',
        ])
            ->whereDoesntHave('despacho')
            ->whereIn('id_estado_pedido', function ($q) {
                $q->select('id_estado_pedido')
                    ->from('estados_pedido')
                    ->whereIn('nombre_estado', ['Aprobado', 'En preparacion']);
            })
            ->orderBy('fecha_pedido', 'asc')
            ->get();

        return $this->jsonResponse($pedidos);
    }

    public function repartidoresDisponibles()
    {
        $repartidores = Repartidor::with([
            'usuario',
            'estado',
            'asignacionVehiculoActiva.vehiculo.modelo.marca',
            'asignacionVehiculoActiva.vehiculo.capacidad',
        ])
            ->whereHas('estado', function ($q) {
                $q->where('nombre_estado', 'Disponible');
            })
            ->get()
            ->map(function ($r) {
                $ultimoControl = ControlRuta::where('id_repartidor', $r->id_repartidor)
                    ->whereHas('estado', function ($q) {
                        $q->whereIn('nombre_estado', ['Borrador', 'Planificada', 'En curso']);
                    })
                    ->with(['despachos.pedido.farmacia'])
                    ->latest('fecha_ruta')
                    ->first();

                $vehiculo = optional($r->asignacionVehiculoActiva)->vehiculo;

                return [
                    'id_repartidor' => $r->id_repartidor,
                    'nombre' => $r->usuario ? trim($r->usuario->nombre . ' ' . $r->usuario->apellido) : '-',
                    'ci' => $r->ci,
                    'firebase_uid' => $r->firebase_uid,
                    'estado' => $r->estado->nombre_estado ?? '-',
                    'vehiculo' => $vehiculo ? [
                        'id_vehiculo' => $vehiculo->id_vehiculo,
                        'placa' => $vehiculo->placa,
                        'modelo' => $vehiculo->modelo->nombre_modelo ?? null,
                        'marca' => $vehiculo->modelo->marca->nombre_marca ?? null,
                        'capacidad_kg' => $vehiculo->capacidad->capacidad_kg ?? null,
                    ] : null,
                    'ruta_activa' => $ultimoControl ? [
                        'id_control_ruta' => $ultimoControl->id_control_ruta,
                        'estado' => $ultimoControl->estado->nombre_estado ?? '-',
                        'despachos_count' => $ultimoControl->despachos->count(),
                    ] : null,
                ];
            });

        return $this->jsonResponse($repartidores);
    }

    public function guardarBorrador(Request $request)
    {
        $request->validate([
            'id_planificacion' => 'nullable|integer|exists:planificacion_rutas,id_planificacion',
            'datos_json' => 'required|json',
        ]);

        $user = $request->user();

        $planificacion = PlanificacionRuta::updateOrCreate(
            ['id_planificacion' => $request->id_planificacion],
            [
                'id_usuario' => $user->id_usuario ?? $user->id,
                'estado' => 'BORRADOR',
                'datos_json' => $request->datos_json,
                'fecha_creacion' => now(),
            ]
        );

        return $this->jsonResponse($planificacion, 'Borrador guardado.');
    }

    public function confirmarRuta(Request $request)
    {
        $request->validate([
            'repartidor_id' => 'required|integer|exists:repartidores,id_repartidor',
            'vehiculo_id' => 'nullable|integer|exists:vehiculos,id_vehiculo',
            'id_planificacion' => 'nullable|integer|exists:planificacion_rutas,id_planificacion',
            'pedidos' => 'required|array|min:1',
            'pedidos.*.id_pedido' => 'required|integer|exists:pedidos,id_pedido',
            'pedidos.*.orden' => 'required|integer|min:1',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $repartidor = Repartidor::with('estado', 'asignacionVehiculoActiva.vehiculo')
                    ->lockForUpdate()
                    ->findOrFail($request->repartidor_id);

                if (($repartidor->estado->nombre_estado ?? '') !== 'Disponible') {
                    return $this->errorResponse('El repartidor no esta disponible.', 422);
                }

                $asignacion = $repartidor->asignacionVehiculoActiva;
                if (!$asignacion || !$asignacion->vehiculo) {
                    return $this->errorResponse('El repartidor no tiene vehiculo asignado.', 422);
                }

                $vehiculoId = (int) ($request->vehiculo_id ?: $asignacion->id_vehiculo);
                if ($vehiculoId !== (int) $asignacion->id_vehiculo) {
                    return $this->errorResponse('El vehiculo no pertenece a la asignacion activa del repartidor.', 422);
                }

                $pedidoIds = collect($request->pedidos)->pluck('id_pedido')->unique()->values();
                if ($pedidoIds->count() !== count($request->pedidos)) {
                    return $this->errorResponse('La planificacion contiene pedidos duplicados.', 422);
                }

                $pedidos = Pedido::with('farmacia', 'despacho')
                    ->whereIn('id_pedido', $pedidoIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id_pedido');

                foreach ($pedidoIds as $pedidoId) {
                    $pedido = $pedidos->get($pedidoId);
                    if (!$pedido) {
                        return $this->errorResponse("El pedido {$pedidoId} no existe.", 422);
                    }

                    if ($pedido->despacho) {
                        return $this->errorResponse("El pedido {$pedidoId} ya tiene despacho.", 422);
                    }

                    if (!$pedido->farmacia || $pedido->farmacia->latitud === null || $pedido->farmacia->longitud === null) {
                        return $this->errorResponse("El pedido {$pedidoId} no puede planificarse porque su farmacia no tiene coordenadas.", 422);
                    }
                }

                $ruta = Ruta::create([
                    'nombre_ruta' => 'Ruta operativa ' . now()->format('Ymd-His') . ' Rep ' . $repartidor->id_repartidor,
                ]);

                $controlRuta = ControlRuta::create([
                    'id_ruta' => $ruta->id_ruta,
                    'fecha_ruta' => now()->toDateString(),
                    'hora_salida' => now()->toTimeString(),
                    'id_repartidor' => $repartidor->id_repartidor,
                    'id_vehiculo' => $vehiculoId,
                    'id_estado_ruta' => 3,
                ]);

                HistorialEstadoRuta::create([
                    'id_control_ruta' => $controlRuta->id_control_ruta,
                    'id_estado_ruta' => 3,
                    'fecha_inicio' => now(),
                ]);

                foreach ($request->pedidos as $pedidoData) {
                    $pedido = $pedidos->get($pedidoData['id_pedido']);
                    $orden = (int) $pedidoData['orden'];
                    $horaEstimada = now()->addMinutes(20 * $orden)->toTimeString();

                    $parada = RutaParada::create([
                        'id_ruta' => $ruta->id_ruta,
                        'id_farmacia' => $pedido->id_farmacia,
                        'orden_parada' => $orden,
                        'hora_estimada' => $horaEstimada,
                    ]);

                    ControlRutaParada::create([
                        'id_control_ruta' => $controlRuta->id_control_ruta,
                        'id_pedido' => $pedido->id_pedido,
                        'id_farmacia' => $pedido->id_farmacia,
                        'orden_parada' => $orden,
                        'hora_estimada' => $horaEstimada,
                    ]);

                    Despacho::create([
                        'id_pedido' => $pedido->id_pedido,
                        'id_parada' => $parada->id_parada,
                        'id_control_ruta' => $controlRuta->id_control_ruta,
                        'fecha_hora_despacho' => now(),
                        'id_estado_despacho' => 1,
                    ]);

                    $pedido->update(['id_estado_pedido' => 4]);
                }

                if ($request->filled('id_planificacion')) {
                    PlanificacionRuta::where('id_planificacion', $request->id_planificacion)->update([
                        'id_control_ruta' => $controlRuta->id_control_ruta,
                        'estado' => 'CONFIRMADA',
                        'fecha_confirmacion' => now(),
                    ]);
                }

                $repartidor->update(['id_estado_repartidor' => 2]);

                return $this->jsonResponse([
                    'id_control_ruta' => $controlRuta->id_control_ruta,
                    'id_ruta' => $ruta->id_ruta,
                    'mensaje' => 'Ruta iniciada exitosamente con ' . count($request->pedidos) . ' pedidos.',
                ], 'Ruta confirmada e iniciada.', 201);
            });
        } catch (\Exception $e) {
            return $this->errorResponse('Error al confirmar ruta: ' . $e->getMessage(), 500);
        }
    }

    public function rutasActivas()
    {
        $rutas = ControlRuta::with([
            'repartidor.usuario',
            'vehiculo.modelo.marca',
            'ruta',
            'estado',
            'despachos.pedido.farmacia',
            'despachos.estado',
        ])
            ->whereHas('estado', function ($q) {
                $q->whereIn('nombre_estado', ['En curso', 'Planificada']);
            })
            ->orderBy('fecha_ruta', 'desc')
            ->get()
            ->map(function ($r) {
                $totalDespachos = $r->despachos->count();
                $completados = $r->despachos->filter(function ($d) {
                    return in_array($d->estado->nombre_estado ?? '', ['Entregado', 'Fallido'], true);
                })->count();
                $repartidor = $r->repartidor;
                $vehiculo = $r->vehiculo;

                return [
                    'id_control_ruta' => $r->id_control_ruta,
                    'fecha' => $r->fecha_ruta,
                    'hora_salida' => $r->hora_salida,
                    'repartidor' => $repartidor ? [
                        'id_repartidor' => $repartidor->id_repartidor,
                        'nombre' => $repartidor->usuario
                            ? trim($repartidor->usuario->nombre . ' ' . $repartidor->usuario->apellido)
                            : '-',
                        'ci' => $repartidor->ci,
                        'firebase_uid' => $repartidor->firebase_uid,
                    ] : null,
                    'vehiculo' => $vehiculo ? [
                        'placa' => $vehiculo->placa,
                        'modelo' => $vehiculo->modelo ? $vehiculo->modelo->nombre_modelo : null,
                        'marca' => $vehiculo->modelo && $vehiculo->modelo->marca ? $vehiculo->modelo->marca->nombre_marca : null,
                    ] : null,
                    'estado' => $r->estado->nombre_estado ?? '-',
                    'progreso' => $totalDespachos > 0 ? round(($completados / $totalDespachos) * 100) : 0,
                    'total_paradas' => $totalDespachos,
                    'completadas' => $completados,
                    'despachos' => $r->despachos->map(function ($d) {
                        return [
                            'id_despacho' => $d->id_despacho,
                            'pedido_id' => $d->id_pedido,
                            'farmacia' => $d->pedido->farmacia->nombre ?? '-',
                            'estado' => $d->estado->nombre_estado ?? '-',
                            'latitud' => $d->pedido->farmacia->latitud ?? null,
                            'longitud' => $d->pedido->farmacia->longitud ?? null,
                        ];
                    }),
                ];
            });

        return $this->jsonResponse($rutas);
    }

    public function detalleRutaActiva($id)
    {
        $control = ControlRuta::with([
            'repartidor.usuario',
            'vehiculo.modelo.marca',
            'ruta',
            'estado',
            'despachos.pedido.farmacia',
            'despachos.pedido.detalles.producto',
            'despachos.estado',
            'despachos.incidencias.tipoIncidencia',
        ])->findOrFail($id);

        $repartidor = $control->repartidor;
        $vehiculo = $control->vehiculo;

        return $this->jsonResponse([
            'id_control_ruta' => $control->id_control_ruta,
            'fecha' => $control->fecha_ruta,
            'hora_salida' => $control->hora_salida,
            'hora_llegada' => $control->hora_llegada_real,
            'repartidor' => $repartidor ? [
                'nombre' => $repartidor->usuario
                    ? trim($repartidor->usuario->nombre . ' ' . $repartidor->usuario->apellido)
                    : '-',
                'ci' => $repartidor->ci,
                'telefono' => $repartidor->usuario->telefono ?? null,
            ] : null,
            'vehiculo' => $vehiculo ? [
                'placa' => $vehiculo->placa,
                'modelo' => $vehiculo->modelo ? $vehiculo->modelo->nombre_modelo : null,
                'marca' => $vehiculo->modelo && $vehiculo->modelo->marca ? $vehiculo->modelo->marca->nombre_marca : null,
            ] : null,
            'estado' => $control->estado->nombre_estado ?? '-',
            'despachos' => $control->despachos->map(function ($d) {
                $detalles = ($d->pedido->detalles ?? collect())->map(function ($det) {
                    return [
                        'producto' => $det->producto->nombre ?? '-',
                        'solicitado' => $det->cantidad_solicitada,
                        'entregado' => $det->cantidad_entregada,
                        'precio' => $det->precio_unitario,
                    ];
                });

                return [
                    'id_despacho' => $d->id_despacho,
                    'id_pedido' => $d->id_pedido,
                    'farmacia' => $d->pedido->farmacia->nombre ?? '-',
                    'direccion' => $d->pedido->farmacia->direccion ?? '-',
                    'latitud' => $d->pedido->farmacia->latitud ?? null,
                    'longitud' => $d->pedido->farmacia->longitud ?? null,
                    'estado' => $d->estado->nombre_estado ?? '-',
                    'total' => $d->pedido->total ?? 0,
                    'detalles' => $detalles,
                    'incidencias' => $d->incidencias->map(function ($i) {
                        return [
                            'tipo' => $i->tipoIncidencia->nombre_tipo ?? '-',
                            'descripcion' => $i->descripcion,
                        ];
                    }),
                ];
            }),
        ]);
    }
}
