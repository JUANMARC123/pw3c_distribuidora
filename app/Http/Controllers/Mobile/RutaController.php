<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\ApiController;
use App\Models\HojaRuta;
use App\Models\DetallePedido;
use App\Models\Despacho;
use App\Models\EvidenciaEntrega;
use App\Models\Incidencia;
use App\Models\Repartidor;
use App\Models\HojaRutaDetalle;
use App\Services\FirebaseSyncService;
use App\Events\RepartidorRegresa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class RutaController extends ApiController
{
    protected FirebaseSyncService $firebaseSync;

    public function __construct(FirebaseSyncService $firebaseSync)
    {
        $this->firebaseSync = $firebaseSync;
    }

    public function rutaActiva(string $firebaseUid): JsonResponse
    {
        $repartidor = Repartidor::where('firebase_uid', $firebaseUid)->first();

        if (!$repartidor) {
            return $this->errorResponse('Repartidor no encontrado', 404);
        }

        $hojaRuta = HojaRuta::where('id_repartidor', $repartidor->id_repartidor)
            ->where('estado_ruta', 'en_progreso')
            ->with(['detalles.pedido.farmacia', 'detalles.despachos'])
            ->first();

        if (!$hojaRuta) {
            return $this->jsonResponse(['ruta' => null, 'mensaje' => 'Sin ruta activa']);
        }

        $paradas = $hojaRuta->detalles->map(function ($detalle) {
            $pedido = $detalle->pedido;
            $despacho = $detalle->despachos->first();
            return [
                'id_detalle' => $detalle->id_detalle,
                'orden_visita' => $detalle->orden_visita,
                'hora_llegada' => $detalle->hora_llegada,
                'hora_salida' => $detalle->hora_salida,
                'pedido' => [
                    'id_pedido' => $pedido->id_pedido,
                    'estado' => $pedido->estado,
                    'total' => $pedido->total,
                    'metodo_pago' => $pedido->metodo_pago,
                    'observaciones' => $pedido->observaciones,
                ],
                'farmacia' => [
                    'id_farmacia' => $pedido->farmacia->id_farmacia,
                    'nombre' => $pedido->farmacia->nombre,
                    'direccion' => $pedido->farmacia->direccion,
                    'latitud' => $pedido->farmacia->latitud,
                    'longitud' => $pedido->farmacia->longitud,
                    'geocerca' => $pedido->farmacia->geocerca,
                ],
                'despacho' => $despacho ? [
                    'id_despacho' => $despacho->id_despacho,
                    'estado_despacho' => $despacho->estado_despacho,
                    'monto_recibido' => $despacho->monto_recibido,
                ] : null,
            ];
        })->sortBy('orden_visita')->values();

        return $this->jsonResponse([
            'ruta' => [
                'id_hoja_ruta' => $hojaRuta->id_hoja_ruta,
                'fecha' => $hojaRuta->fecha,
                'hora_salida' => $hojaRuta->hora_salida,
                'estado_ruta' => $hojaRuta->estado_ruta,
                'paradas' => $paradas,
            ],
        ]);
    }

    public function registrarLlegada(Request $request): JsonResponse
    {
        $request->validate([
            'firebase_uid' => 'required|string',
            'id_detalle_hoja_ruta' => 'required|integer|exists:hoja_ruta_detalles,id_detalle',
        ]);

        $detalle = HojaRutaDetalle::findOrFail($request->id_detalle_hoja_ruta);
        $repartidor = Repartidor::where('firebase_uid', $request->firebase_uid)->first();

        if (!$repartidor) {
            return $this->errorResponse('Repartidor no encontrado', 404);
        }

        if ($detalle->hojaRuta->id_repartidor !== $repartidor->id_repartidor) {
            return $this->errorResponse('Esta parada no pertenece a tu ruta', 403);
        }

        $detalle->update(['hora_llegada' => now()->format('H:i:s')]);

        $despacho = Despacho::firstOrCreate(
            ['id_detalle_hoja_ruta' => $detalle->id_detalle],
            [
                'id_pedido' => $detalle->id_pedido,
                'fecha_hora_despacho' => now(),
                'estado_despacho' => 'pendiente',
                'monto_recibido' => 0,
            ]
        );

        $this->firebaseSync->actualizarEstadoParada(
            $request->firebase_uid,
            $detalle->id_detalle,
            'llegada'
        );

        return $this->jsonResponse([
            'mensaje' => 'Llegada registrada',
            'id_detalle' => $detalle->id_detalle,
            'hora_llegada' => $detalle->hora_llegada,
            'id_despacho' => $despacho->id_despacho,
        ]);
    }

    public function regresoAlmacen(Request $request): JsonResponse
    {
        $request->validate([
            'firebase_uid' => 'required|string',
        ]);

        $repartidor = Repartidor::where('firebase_uid', $request->firebase_uid)->first();
        if (!$repartidor) {
            return $this->errorResponse('Repartidor no encontrado', 404);
        }

        $hojaRuta = HojaRuta::with('repartidor', 'detalles')
            ->where('id_repartidor', $repartidor->id_repartidor)
            ->where('estado_ruta', 'en_progreso')
            ->first();

        if (!$hojaRuta) {
            return $this->errorResponse('No hay ruta activa en progreso', 404);
        }

        $hojaRuta->update([
            'estado_ruta' => 'completado',
            'hora_llegada' => now()->toTimeString(),
        ]);

        $asignacion = $hojaRuta->repartidor->asignacionActiva;
        $vehiculoId = $asignacion ? $asignacion->id_vehiculo : 0;

        event(new RepartidorRegresa($hojaRuta, $hojaRuta->id_repartidor, $vehiculoId));

        $this->firebaseSync->limpiarViaje($hojaRuta);

        return $this->jsonResponse([
            'mensaje' => 'Ruta completada. Regreso al almacén registrado.',
        ]);
    }

    public function detallePedido(int $pedidoId): JsonResponse
    {
        $pedido = \App\Models\Pedido::with(['detalles.producto', 'farmacia'])->findOrFail($pedidoId);

        return $this->jsonResponse([
            'pedido' => [
                'id_pedido' => $pedido->id_pedido,
                'estado' => $pedido->estado,
                'metodo_pago' => $pedido->metodo_pago,
                'subtotal' => $pedido->subtotal,
                'descuento' => $pedido->descuento,
                'total' => $pedido->total,
                'observaciones' => $pedido->observaciones,
                'farmacia' => [
                    'nombre' => $pedido->farmacia->nombre,
                    'direccion' => $pedido->farmacia->direccion,
                ],
                'detalles' => $pedido->detalles->map(function ($detalle) {
                    return [
                        'id_detalle' => $detalle->id_detalle,
                        'producto' => $detalle->producto?->nombre ?? 'Producto',
                        'cantidad_solicitada' => $detalle->cantidad_solicitada,
                        'cantidad_entregada' => $detalle->cantidad_entregada,
                        'cantidad_rechazada' => $detalle->cantidad_rechazada,
                        'motivo_rechazo' => $detalle->motivo_rechazo,
                        'precio_unitario' => $detalle->precio_unitario,
                        'subtotal' => $detalle->subtotal,
                    ];
                }),
            ],
        ]);
    }

    public function actualizarDetalleEntrega(Request $request, int $detalleId): JsonResponse
    {
        $request->validate([
            'firebase_uid' => 'required|string',
            'cantidad_entregada' => 'required|integer|min:0',
            'cantidad_rechazada' => 'required|integer|min:0',
            'motivo_rechazo' => 'nullable|string|max:255',
        ]);

        $detalle = DetallePedido::findOrFail($detalleId);

        $detalle->update([
            'cantidad_entregada' => $request->cantidad_entregada,
            'cantidad_rechazada' => $request->cantidad_rechazada,
            'motivo_rechazo' => $request->motivo_rechazo,
        ]);

        $detalle->subtotal = $detalle->cantidad_entregada * $detalle->precio_unitario;
        $detalle->save();

        $pedido = $detalle->pedido;
        $pedido->subtotal = $pedido->detalles->sum(fn($d) => $d->cantidad_entregada * $d->precio_unitario);
        $pedido->total = $pedido->subtotal - $pedido->descuento;
        $pedido->save();

        return $this->jsonResponse([
            'mensaje' => 'Detalle de entrega actualizado',
            'id_detalle' => $detalle->id_detalle,
            'cantidad_entregada' => $detalle->cantidad_entregada,
            'cantidad_rechazada' => $detalle->cantidad_rechazada,
        ]);
    }

    public function storeEvidencia(Request $request, int $despachoId): JsonResponse
    {
        $request->validate([
            'firebase_uid' => 'required|string',
            'tipo_evidencia' => 'required|string|in:firma,foto,sello',
            'archivo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $despacho = Despacho::find($despachoId);
        if (!$despacho) {
            return $this->errorResponse('Despacho no encontrado', 404);
        }

        $path = $request->file('archivo')->store("evidencias/despacho_{$despachoId}", 'public');

        $evidencia = EvidenciaEntrega::create([
            'id_despacho' => $despachoId,
            'tipo_evidencia' => $request->tipo_evidencia,
            'archivo' => $path,
        ]);

        return $this->jsonResponse([
            'mensaje' => 'Evidencia guardada',
            'id_evidencia' => $evidencia->id_evidencia,
            'archivo' => Storage::url($path),
        ], 'Evidencia guardada', 201);
    }

    public function storeIncidencia(Request $request, int $despachoId): JsonResponse
    {
        $request->validate([
            'firebase_uid' => 'required|string',
            'tipo_incidencia' => 'required|string|in:cliente_ausente,producto_dañado,producto_incompleto,direccion_incorrecta,rechazo_cliente,otro',
            'descripcion' => 'required|string|max:500',
            'archivo' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $despacho = Despacho::find($despachoId);
        if (!$despacho) {
            return $this->errorResponse('Despacho no encontrado', 404);
        }

        $path = null;
        if ($request->hasFile('archivo')) {
            $path = $request->file('archivo')->store("incidencias/despacho_{$despachoId}", 'public');
        }

        $incidencia = Incidencia::create([
            'id_despacho' => $despachoId,
            'tipo_incidencia' => $request->tipo_incidencia,
            'descripcion' => $request->descripcion,
            'archivo' => $path,
        ]);

        return $this->jsonResponse([
            'mensaje' => 'Incidencia registrada',
            'id_incidencia' => $incidencia->id_incidencia,
        ], 'Incidencia registrada', 201);
    }
}
