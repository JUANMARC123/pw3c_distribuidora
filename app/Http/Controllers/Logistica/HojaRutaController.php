<?php

namespace App\Http\Controllers\Logistica;

use App\Events\RutaIniciada;
use App\Events\EntregaCompletada;
use App\Events\EntregaFallida;
use App\Events\DevolucionProducto;
use App\Events\RepartidorRegresa;
use App\Http\Controllers\ApiController;
use App\Models\Despacho;
use App\Models\HojaRuta;
use App\Models\HojaRutaDetalle;
use App\Models\Pedido;
use App\Models\Repartidor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HojaRutaController extends ApiController
{
    public function index(Request $request)
    {
        $query = HojaRuta::with('repartidor.usuario', 'vehiculo', 'detalles.pedido.farmacia');

        if ($request->filled('estado_ruta')) {
            $query->where('estado_ruta', $request->estado_ruta);
        }

        if ($request->filled('id_repartidor')) {
            $query->where('id_repartidor', $request->id_repartidor);
        }

        if ($request->filled('fecha')) {
            $query->where('fecha', $request->fecha);
        }

        return $this->safePaginate($query->orderBy('fecha', 'desc'), $request);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'id_repartidor' => 'required|integer|exists:repartidores,id_repartidor',
            'id_vehiculo' => 'required|integer|exists:vehiculos,id_vehiculo',
            'pedidos' => 'required|array|min:1',
            'pedidos.*.id_pedido' => 'required|integer|exists:pedidos,id_pedido',
            'pedidos.*.orden_visita' => 'required|integer|min:1',
        ]);

        try {
            return DB::transaction(function () use ($request, $validated) {
                $hojaRuta = HojaRuta::create([
                    'fecha' => $validated['fecha'],
                    'hora_salida' => now()->toTimeString(),
                    'id_repartidor' => $validated['id_repartidor'],
                    'id_vehiculo' => $validated['id_vehiculo'],
                    'estado_ruta' => 'planificado',
                ]);

                $pedidoIds = [];

                foreach ($request->pedidos as $pedidoData) {
                    $pedido = Pedido::findOrFail($pedidoData['id_pedido']);

                    $detalle = HojaRutaDetalle::create([
                        'id_hoja_ruta' => $hojaRuta->id_hoja_ruta,
                        'id_pedido' => $pedidoData['id_pedido'],
                        'orden_visita' => $pedidoData['orden_visita'],
                    ]);

                    $despachoExistente = Despacho::where('id_pedido', $pedidoData['id_pedido'])->first();
                    if ($despachoExistente) {
                        $despachoExistente->update([
                            'id_detalle_hoja_ruta' => $detalle->id_detalle,
                            'fecha_hora_despacho' => now(),
                            'estado_despacho' => 'pendiente',
                        ]);
                    } else {
                        Despacho::create([
                            'id_pedido' => $pedidoData['id_pedido'],
                            'id_detalle_hoja_ruta' => $detalle->id_detalle,
                            'fecha_hora_despacho' => now(),
                            'estado_despacho' => 'pendiente',
                        ]);
                    }

                    $pedidoIds[] = $pedidoData['id_pedido'];
                }

                return $this->jsonResponse(
                    $hojaRuta->load('repartidor.usuario', 'vehiculo', 'detalles.pedido.farmacia'),
                    'Hoja de ruta creada exitosamente.',
                    201
                );
            });
        } catch (\Exception $e) {
            return $this->errorResponse('Error al crear hoja de ruta: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $hojaRuta = HojaRuta::with([
            'repartidor.usuario',
            'vehiculo',
            'detalles.pedido.farmacia',
            'detalles.pedido.detalles.producto',
            'detalles.despachos.evidencias',
            'detalles.despachos.incidencias',
            'caja.gastos',
        ])->findOrFail($id);

        return $this->jsonResponse($hojaRuta);
    }

    public function update(Request $request, $id)
    {
        $hojaRuta = HojaRuta::findOrFail($id);

        $validated = $request->validate([
            'hora_salida' => 'sometimes',
            'hora_llegada' => 'nullable',
            'estado_ruta' => 'sometimes|in:planificado,en_progreso,completado,cancelado',
        ]);

        $hojaRuta->update($validated);

        return $this->jsonResponse($hojaRuta, 'Hoja de ruta actualizada.');
    }

    public function destroy($id)
    {
        try {
            $hojaRuta = HojaRuta::findOrFail($id);
            $hojaRuta->delete();
            return $this->jsonResponse(null, 'Hoja de ruta eliminada.');
        } catch (\Exception $e) {
            return $this->errorResponse('No se puede eliminar la hoja de ruta.', 409);
        }
    }

    public function iniciarRuta($id)
    {
        $hojaRuta = HojaRuta::with('repartidor', 'vehiculo', 'detalles')->findOrFail($id);

        if ($hojaRuta->estado_ruta !== 'planificado') {
            return $this->errorResponse('La ruta no está en estado planificado.', 422);
        }

        $hojaRuta->update([
            'estado_ruta' => 'en_progreso',
            'hora_salida' => now()->toTimeString(),
        ]);

        $pedidoIds = $hojaRuta->detalles->pluck('id_pedido')->toArray();

        event(new RutaIniciada(
            $hojaRuta,
            $pedidoIds,
            $hojaRuta->id_vehiculo
        ));

        return $this->jsonResponse($hojaRuta->load('repartidor.usuario', 'vehiculo', 'detalles.pedido.farmacia'), 'Ruta iniciada.');
    }

    public function registrarLlegada(Request $request, $id)
    {
        $request->validate([
            'id_detalle' => 'required|integer|exists:hoja_ruta_detalles,id_detalle',
        ]);

        $detalle = HojaRutaDetalle::where('id_hoja_ruta', $id)
            ->findOrFail($request->id_detalle);

        $detalle->update(['hora_llegada' => now()->toTimeString()]);

        return $this->jsonResponse($detalle, 'Llegada registrada.');
    }

    public function marcarEntregaCompletada(Request $request, $id)
    {
        $request->validate([
            'id_detalle' => 'required|integer|exists:hoja_ruta_detalles,id_detalle',
            'id_despacho' => 'required|integer|exists:despachos,id_despacho',
            'id_pedido' => 'required|integer|exists:pedidos,id_pedido',
        ]);

        $detalle = HojaRutaDetalle::findOrFail($request->id_detalle);

        event(new EntregaCompletada($detalle, (int) $request->id_despacho, (int) $request->id_pedido));

        return $this->jsonResponse(null, 'Entrega completada.');
    }

    public function marcarEntregaFallida(Request $request, $id)
    {
        $request->validate([
            'id_detalle' => 'required|integer|exists:hoja_ruta_detalles,id_detalle',
            'id_despacho' => 'required|integer|exists:despachos,id_despacho',
            'id_pedido' => 'required|integer|exists:pedidos,id_pedido',
            'motivo' => 'nullable|string|max:500',
        ]);

        $detalle = HojaRutaDetalle::findOrFail($request->id_detalle);

        event(new EntregaFallida($detalle, (int) $request->id_despacho, (int) $request->id_pedido, $request->motivo ?? ''));

        return $this->jsonResponse(null, 'Entrega fallida registrada.');
    }

    public function marcarDevolucion(Request $request, $id)
    {
        $request->validate([
            'id_detalle' => 'required|integer|exists:hoja_ruta_detalles,id_detalle',
            'id_despacho' => 'required|integer|exists:despachos,id_despacho',
            'id_pedido' => 'required|integer|exists:pedidos,id_pedido',
        ]);

        $detalle = HojaRutaDetalle::findOrFail($request->id_detalle);

        event(new DevolucionProducto($detalle, (int) $request->id_despacho, (int) $request->id_pedido));

        return $this->jsonResponse(null, 'Devolución registrada.');
    }

    public function regresoAlmacen($id)
    {
        $hojaRuta = HojaRuta::with('repartidor', 'detalles')->findOrFail($id);

        $hojaRuta->update([
            'estado_ruta' => 'completado',
            'hora_llegada' => now()->toTimeString(),
        ]);

        $asignacion = $hojaRuta->repartidor->asignacionActiva;
        $vehiculoId = $asignacion ? $asignacion->id_vehiculo : 0;

        event(new RepartidorRegresa($hojaRuta, $hojaRuta->id_repartidor, $vehiculoId));

        return $this->jsonResponse(null, 'Regreso al almacén registrado. Ruta completada.');
    }

    public function rutasActivas()
    {
        $rutas = HojaRuta::with([
            'repartidor.usuario',
            'vehiculo',
            'detalles.pedido.farmacia',
            'detalles.despachos',
        ])
            ->whereIn('estado_ruta', ['planificado', 'en_progreso'])
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(function ($r) {
                $totalDespachos = $r->detalles->sum(function ($d) {
                    return $d->despachos->count();
                });
                $completados = $r->detalles->sum(function ($d) {
                    return $d->despachos->whereIn('estado_despacho', ['entregado', 'incidencia'])->count();
                });

                return [
                    'id_hoja_ruta' => $r->id_hoja_ruta,
                    'fecha' => $r->fecha,
                    'hora_salida' => $r->hora_salida,
                    'repartidor' => $r->repartidor ? [
                        'id_repartidor' => $r->repartidor->id_repartidor,
                        'nombre' => $r->repartidor->usuario
                            ? trim($r->repartidor->usuario->nombre . ' ' . $r->repartidor->usuario->apellido)
                            : '-',
                        'ci' => $r->repartidor->ci,
                        'firebase_uid' => $r->repartidor->firebase_uid,
                    ] : null,
                    'vehiculo' => $r->vehiculo ? [
                        'id_vehiculo' => $r->vehiculo->id_vehiculo,
                        'placa' => $r->vehiculo->placa,
                    ] : null,
                    'estado_ruta' => $r->estado_ruta,
                    'progreso' => $totalDespachos > 0 ? round(($completados / $totalDespachos) * 100) : 0,
                    'total_paradas' => $totalDespachos,
                    'completadas' => $completados,
                ];
            });

        return $this->jsonResponse($rutas);
    }

    public function pedidosPendientes()
    {
        $pedidos = Pedido::with('farmacia', 'detalles.producto')
            ->whereDoesntHave('despacho')
            ->where('estado', 'aprobado')
            ->orderBy('fecha_pedido', 'asc')
            ->get();

        return $this->jsonResponse($pedidos);
    }

    public function repartidoresDisponibles()
    {
        $repartidores = Repartidor::with('usuario', 'asignacionActiva.vehiculo')
            ->where('estado', 'activo')
            ->get()
            ->map(function ($r) {
                return [
                    'id_repartidor' => $r->id_repartidor,
                    'nombre' => $r->usuario ? trim($r->usuario->nombre . ' ' . $r->usuario->apellido) : '-',
                    'ci' => $r->ci,
                    'firebase_uid' => $r->firebase_uid,
                    'estado' => $r->estado,
                    'vehiculo' => $r->asignacionActiva && $r->asignacionActiva->vehiculo ? [
                        'id_vehiculo' => $r->asignacionActiva->vehiculo->id_vehiculo,
                        'placa' => $r->asignacionActiva->vehiculo->placa,
                        'marca' => $r->asignacionActiva->vehiculo->marca,
                        'modelo' => $r->asignacionActiva->vehiculo->modelo,
                    ] : null,
                ];
            });

        return $this->jsonResponse($repartidores);
    }
}
