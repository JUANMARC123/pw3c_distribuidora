<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\ApiController;
use App\Models\Logistica\ControlRuta;
use App\Models\Logistica\PlanificacionRuta;
use App\Models\Logistica\RutaParada;
use App\Models\Pedido\Pedido;
use App\Models\Pedido\DetallePedido;
use App\Models\Despacho\Despacho;
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
            'vehiculoActual' => function ($q) {
                $q->with('modelo.marca', 'capacidad');
            },
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

                return [
                    'id_repartidor' => $r->id_repartidor,
                    'nombre' => $r->usuario ? trim($r->usuario->nombre . ' ' . $r->usuario->apellido) : '—',
                    'ci' => $r->ci,
                    'estado' => $r->estado->nombre_estado ?? '—',
                    'vehiculo' => $r->vehiculoActual ? [
                        'placa' => $r->vehiculoActual->placa,
                        'modelo' => $r->vehiculoActual->modelo->nombre_modelo ?? null,
                        'marca' => $r->vehiculoActual->modelo->marca->nombre_marca ?? null,
                        'capacidad_kg' => $r->vehiculoActual->capacidad->capacidad_kg ?? null,
                    ] : null,
                    'ruta_activa' => $ultimoControl ? [
                        'id_control_ruta' => $ultimoControl->id_control_ruta,
                        'estado' => $ultimoControl->estado->nombre_estado ?? '—',
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
            'vehiculo_id' => 'required|integer|exists:vehiculos,id_vehiculo',
            'id_ruta' => 'nullable|integer|exists:rutas,id_ruta',
            'pedidos' => 'required|array|min:1',
            'pedidos.*.id_pedido' => 'required|integer|exists:pedidos,id_pedido',
            'pedidos.*.orden' => 'required|integer|min:1',
        ]);

        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $user) {
                $controlRuta = ControlRuta::create([
                    'id_ruta' => $request->id_ruta ?? 1,
                    'fecha_ruta' => now()->toDateString(),
                    'hora_salida' => now()->toTimeString(),
                    'id_repartidor' => $request->repartidor_id,
                    'id_vehiculo' => $request->vehiculo_id,
                    'id_estado_ruta' => 3,
                ]);

                foreach ($request->pedidos as $pedidoData) {
                    $pedido = Pedido::findOrFail($pedidoData['id_pedido']);

                    $parada = RutaParada::create([
                        'id_ruta' => $request->id_ruta ?? 1,
                        'id_farmacia' => $pedido->id_farmacia,
                        'orden_parada' => $pedidoData['orden'],
                        'hora_estimada' => now()->toTimeString(),
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

                $repartidor = Repartidor::find($request->repartidor_id);
                if ($repartidor) {
                    $repartidor->update(['id_estado_repartidor' => 2]);
                }

                return $this->jsonResponse([
                    'id_control_ruta' => $controlRuta->id_control_ruta,
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
                    return in_array($d->estado->nombre_estado ?? '', ['Entregado', 'Fallido']);
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
                            : '—',
                        'ci' => $repartidor->ci,
                    ] : null,
                    'vehiculo' => $vehiculo ? [
                        'placa' => $vehiculo->placa,
                        'modelo' => $vehiculo->modelo ? $vehiculo->modelo->nombre_modelo : null,
                        'marca' => $vehiculo->modelo && $vehiculo->modelo->marca ? $vehiculo->modelo->marca->nombre_marca : null,
                    ] : null,
                    'estado' => $r->estado->nombre_estado ?? '—',
                    'progreso' => $totalDespachos > 0 ? round(($completados / $totalDespachos) * 100) : 0,
                    'total_paradas' => $totalDespachos,
                    'completadas' => $completados,
                    'despachos' => $r->despachos->map(function ($d) {
                        return [
                            'id_despacho' => $d->id_despacho,
                            'pedido_id' => $d->id_pedido,
                            'farmacia' => $d->pedido->farmacia->nombre ?? '—',
                            'estado' => $d->estado->nombre_estado ?? '—',
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
            'despachos.incidencias',
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
                    : '—',
                'ci' => $repartidor->ci,
                'telefono' => $repartidor->usuario->telefono ?? null,
            ] : null,
            'vehiculo' => $vehiculo ? [
                'placa' => $vehiculo->placa,
                'modelo' => $vehiculo->modelo ? $vehiculo->modelo->nombre_modelo : null,
                'marca' => $vehiculo->modelo && $vehiculo->modelo->marca ? $vehiculo->modelo->marca->nombre_marca : null,
            ] : null,
            'estado' => $control->estado->nombre_estado ?? '—',
            'despachos' => $control->despachos->map(function ($d) {
                $detalles = ($d->pedido->detalles ?? collect())->map(function ($det) {
                    return [
                        'producto' => $det->producto->nombre ?? '—',
                        'solicitado' => $det->cantidad_solicitada,
                        'entregado' => $det->cantidad_entregada,
                        'precio' => $det->precio_unitario,
                    ];
                });

                return [
                    'id_despacho' => $d->id_despacho,
                    'id_pedido' => $d->id_pedido,
                    'farmacia' => $d->pedido->farmacia->nombre ?? '—',
                    'direccion' => $d->pedido->farmacia->direccion ?? '—',
                    'latitud' => $d->pedido->farmacia->latitud ?? null,
                    'longitud' => $d->pedido->farmacia->longitud ?? null,
                    'estado' => $d->estado->nombre_estado ?? '—',
                    'total' => $d->pedido->total ?? 0,
                    'detalles' => $detalles,
                    'incidencias' => $d->incidencias->map(function ($i) {
                        return [
                            'tipo' => $i->tipo->nombre_tipo ?? '—',
                            'descripcion' => $i->descripcion,
                        ];
                    }),
                ];
            }),
        ]);
    }
}
