<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\ApiController;
use App\Models\HojaRuta;
use App\Models\Despacho;
use App\Models\EvidenciaEntrega;
use App\Models\Pedido;
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
                    'metodo_pago' => $despacho?->metodo_pago,
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
                    'metodo_pago' => $despacho->metodo_pago,
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
                'cobrado_efectivo' => (float) $hojaRuta->cobrado_efectivo,
                'cobrado_qr' => (float) $hojaRuta->cobrado_qr,
                'total_cobrado' => (float) $hojaRuta->cobrado_efectivo + (float) $hojaRuta->cobrado_qr,
                'paradas' => $paradas,
            ],
        ]);
    }

    public function getPedidoDetalle(int $pedidoId): JsonResponse
    {
        $pedido = Pedido::with(['farmacia', 'detalles'])
            ->find($pedidoId);

        if (!$pedido) {
            return $this->errorResponse('Pedido no encontrado', 404);
        }

        return $this->jsonResponse([
            'pedido' => [
                'id_pedido' => $pedido->id_pedido,
                'fecha' => $pedido->fecha,
                'total' => $pedido->total,
                'estado' => $pedido->estado,
                'observaciones' => $pedido->observaciones,
                'farmacia' => $pedido->farmacia ? [
                    'id_farmacia' => $pedido->farmacia->id_farmacia,
                    'nombre' => $pedido->farmacia->nombre,
                    'direccion' => $pedido->farmacia->direccion,
                    'latitud' => $pedido->farmacia->latitud,
                    'longitud' => $pedido->farmacia->longitud,
                    'geocerca' => $pedido->farmacia->geocerca,
                ] : null,
                'detalles' => $pedido->detalles->map(function ($detalle) {
                    return [
                        'id_detalle' => $detalle->id_detalle,
                        'producto' => $detalle->producto->nombre ?? '',
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

    public function registrarEntrega(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'firebase_uid' => 'required|string',
            'id_detalle_hoja_ruta' => 'required|integer|exists:hoja_ruta_detalles,id_detalle',
            'id_pedido' => 'required|integer|exists:pedidos,id_pedido',
            'id_despacho' => 'nullable|integer|exists:despachos,id_despacho',
            'metodo_pago' => 'required|in:efectivo,qr,transferencia,credito',
            'monto_recibido' => 'required|numeric|min:0',
        ]);

        $repartidor = Repartidor::where('firebase_uid', $validated['firebase_uid'])->first();

        if (!$repartidor) {
            return $this->errorResponse('Repartidor no encontrado', 404);
        }

        $detalle = HojaRutaDetalle::with('pedido')
            ->findOrFail($validated['id_detalle_hoja_ruta']);

        if ($detalle->hojaRuta->id_repartidor !== $repartidor->id_repartidor) {
            return $this->errorResponse('Esta parada no pertenece a tu ruta', 403);
        }

        if ((int) $detalle->id_pedido !== (int) $validated['id_pedido']) {
            return $this->errorResponse('El pedido no corresponde a esta parada', 422);
        }

        $despacho = Despacho::where('id_detalle_hoja_ruta', $detalle->id_detalle)->first();

        if (!$despacho && !empty($validated['id_despacho'])) {
            $despacho = Despacho::find($validated['id_despacho']);
        }

        if (!$despacho) {
            $despacho = Despacho::create([
                'id_pedido' => $detalle->id_pedido,
                'id_detalle_hoja_ruta' => $detalle->id_detalle,
                'fecha_hora_despacho' => now(),
                'estado_despacho' => 'pendiente',
                'monto_recibido' => 0,
            ]);
        }

        $despacho->update([
            'estado_despacho' => 'entregado',
            'metodo_pago' => $validated['metodo_pago'],
            'monto_recibido' => $validated['monto_recibido'],
        ]);

        $detalle->pedido->update(['estado' => 'entregado']);

        $hojaRuta = $detalle->hojaRuta;
        $monto = (float) $validated['monto_recibido'];

        if ($validated['metodo_pago'] === 'efectivo' && $monto > 0) {
            $hojaRuta->increment('cobrado_efectivo', $monto);
        } elseif ($validated['metodo_pago'] === 'qr' && $monto > 0) {
            $hojaRuta->increment('cobrado_qr', $monto);
        }

        $hojaRuta->asegurarVinculoCaja();

        $this->firebaseSync->actualizarEstadoParada(
            $validated['firebase_uid'],
            $detalle->id_detalle,
            'entregado'
        );

        return $this->jsonResponse([
            'mensaje' => 'Entrega registrada correctamente',
            'id_despacho' => $despacho->id_despacho,
            'estado_despacho' => $despacho->estado_despacho,
            'metodo_pago' => $despacho->metodo_pago,
            'monto_recibido' => $despacho->monto_recibido,
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

        $hojaRuta->asegurarVinculoCaja();

        $asignacion = $hojaRuta->repartidor->asignacionActiva;
        $vehiculoId = $asignacion ? $asignacion->id_vehiculo : 0;

        event(new RepartidorRegresa($hojaRuta, $hojaRuta->id_repartidor, $vehiculoId));

        $this->firebaseSync->limpiarViaje($hojaRuta);

        return $this->jsonResponse([
            'mensaje' => 'Ruta completada. Regreso al almacén registrado.',
        ]);
    }

    public function entregaFallida(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'firebase_uid' => 'required|string',
            'id_detalle_hoja_ruta' => 'required|integer|exists:hoja_ruta_detalles,id_detalle',
            'id_pedido' => 'required|integer|exists:pedidos,id_pedido',
            'id_despacho' => 'nullable|integer|exists:despachos,id_despacho',
            'motivo' => 'nullable|string|max:500',
        ]);

        $repartidor = Repartidor::where('firebase_uid', $validated['firebase_uid'])->first();

        if (!$repartidor) {
            return $this->errorResponse('Repartidor no encontrado', 404);
        }

        $detalle = HojaRutaDetalle::with('pedido')->findOrFail($validated['id_detalle_hoja_ruta']);

        if ($detalle->hojaRuta->id_repartidor !== $repartidor->id_repartidor) {
            return $this->errorResponse('Esta parada no pertenece a tu ruta', 403);
        }

        $despacho = Despacho::where('id_detalle_hoja_ruta', $detalle->id_detalle)->first();

        if (!$despacho && !empty($validated['id_despacho'])) {
            $despacho = Despacho::find($validated['id_despacho']);
        }

        if (!$despacho) {
            $despacho = Despacho::create([
                'id_pedido' => $detalle->id_pedido,
                'id_detalle_hoja_ruta' => $detalle->id_detalle,
                'fecha_hora_despacho' => now(),
                'estado_despacho' => 'pendiente',
                'monto_recibido' => 0,
            ]);
        }

        $despacho->update([
            'estado_despacho' => 'incidencia',
            'metodo_pago' => null,
            'monto_recibido' => 0,
        ]);

        $detalle->pedido->update(['estado' => 'cancelado']);

        return $this->jsonResponse([
            'mensaje' => 'Entrega marcada como fallida',
            'id_despacho' => $despacho->id_despacho,
            'estado_despacho' => $despacho->estado_despacho,
        ]);
    }

    public function storeEvidencia(Request $request, int $despachoId): JsonResponse
    {
        $request->validate([
            'firebase_uid' => 'required|string',
            'tipo_evidencia' => 'required|string|in:firma,foto,sello',
            'archivo' => 'required|file|mimes:jpg,jpeg,png,pdf,webp,heic,heif|max:10240',
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
}
