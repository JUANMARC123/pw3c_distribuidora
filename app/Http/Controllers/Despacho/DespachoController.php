<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\ApiController;
use App\Models\Despacho\Despacho;
use App\Models\Despacho\HistorialEstadoDespacho;
use App\Models\Pedido\Pedido;
use App\Models\Logistica\RutaParada;
use App\Models\Logistica\ControlRuta;
use Illuminate\Http\Request;
use App\Services\AuditService;

class DespachoController extends ApiController
{
    public function index(Request $request)
    {
        $query = Despacho::with('pedido.farmacia', 'estado', 'controlRuta.ruta');

        if ($request->filled('id_estado_despacho')) {
            $query->where('id_estado_despacho', $request->id_estado_despacho);
        }

        if ($request->filled('id_pedido')) {
            $query->where('id_pedido', $request->id_pedido);
        }

        if ($request->filled('id_control_ruta')) {
            $query->where('id_control_ruta', $request->id_control_ruta);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_hora_despacho', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_hora_despacho', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        return $this->paginatedResponse(
            $query->orderBy('fecha_hora_despacho', 'desc')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_pedido' => 'required|integer|exists:pedidos,id_pedido|unique:despachos,id_pedido',
            'id_parada' => 'required|integer|exists:ruta_paradas,id_parada',
            'id_control_ruta' => 'required|integer|exists:control_rutas,id_control_ruta',
            'id_estado_despacho' => 'required|integer|exists:estados_despacho,id_estado_despacho',
        ]);

        // RN-053: La farmacia del pedido debe coincidir con la farmacia de la parada
        $pedido = Pedido::findOrFail($data['id_pedido']);
        $parada = RutaParada::findOrFail($data['id_parada']);
        if ((int)$pedido->id_farmacia !== (int)$parada->id_farmacia) {
            return $this->errorResponse(
                'La farmacia del pedido no coincide con la farmacia de la parada seleccionada.', 422
            );
        }

        // RN-054: La parada debe pertenecer a la misma ruta del control de ruta
        $controlRuta = ControlRuta::findOrFail($data['id_control_ruta']);
        if ((int)$parada->id_ruta !== (int)$controlRuta->id_ruta) {
            return $this->errorResponse(
                'La parada seleccionada no pertenece a la ruta del control de ruta.', 422
            );
        }

        $data['fecha_hora_despacho'] = now();

        $despacho = Despacho::create($data);

        AuditService::log(auth()->id(), 'crear', 'despachos', $despacho->id_despacho);

        HistorialEstadoDespacho::create([
            'id_despacho' => $despacho->id_despacho,
            'id_estado_despacho' => $data['id_estado_despacho'],
            'fecha_inicio' => now(),
        ]);

        return $this->jsonResponse(
            $despacho->load('pedido.farmacia', 'estado', 'controlRuta.ruta', 'parada.farmacia'),
            'Despacho creado exitosamente.',
            201
        );
    }

    public function show($id)
    {
        $despacho = Despacho::with([
            'pedido.farmacia',
            'pedido.usuario',
            'parada.farmacia',
            'controlRuta.ruta',
            'controlRuta.repartidor.usuario',
            'controlRuta.vehiculo',
            'estado',
            'historiales.estado',
            'incidencias.tipoIncidencia',
            'evidencias.tipoEvidencia',
        ])->findOrFail($id);

        return $this->jsonResponse($despacho);
    }

    public function update(Request $request, $id)
    {
        $despacho = Despacho::findOrFail($id);

        $data = $request->validate([
            'id_parada' => 'sometimes|integer|exists:ruta_paradas,id_parada',
            'id_control_ruta' => 'sometimes|integer|exists:control_rutas,id_control_ruta',
        ]);

        $despacho->update($data);

        AuditService::log(auth()->id(), 'editar', 'despachos', $despacho->id_despacho);

        return $this->jsonResponse(
            $despacho->load('pedido.farmacia', 'estado'),
            'Despacho actualizado exitosamente.'
        );
    }

    public function destroy($id)
    {
        $despacho = Despacho::findOrFail($id);
        $despacho->delete();

        AuditService::log(auth()->id(), 'eliminar', 'despachos', $id);

        return $this->jsonResponse(null, 'Despacho eliminado exitosamente.');
    }

    private const TRANSICIONES_DESPACHO = [
        1 => [2],
        2 => [3, 4],
        3 => [],
        4 => [],
    ];

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'id_estado_despacho' => 'required|integer|exists:estados_despacho,id_estado_despacho',
        ]);

        $despacho = Despacho::findOrFail($id);
        $nuevoEstado = (int) $request->id_estado_despacho;
        $estadoActual = (int) $despacho->id_estado_despacho;

        $transicionesValidas = self::TRANSICIONES_DESPACHO[$estadoActual] ?? [];
        if (!empty($transicionesValidas) && !in_array($nuevoEstado, $transicionesValidas)) {
            return $this->errorResponse(
                "Transición de estado no válida. No se puede cambiar de " .
                "{$despacho->estado->nombre_estado} al estado solicitado.",
                422
            );
        }

        if ($nuevoEstado === $estadoActual) {
            return $this->errorResponse('El despacho ya se encuentra en este estado.', 422);
        }

        // RN-055: Despacho "Entregado" (estado 3) requiere al menos una evidencia
        if ($nuevoEstado === 3 && $despacho->evidencias()->count() === 0) {
            return $this->errorResponse(
                'No se puede marcar como Entregado sin registrar al menos una evidencia de entrega.', 422
            );
        }

        $historialActual = HistorialEstadoDespacho::where('id_despacho', $id)
            ->whereNull('fecha_fin')
            ->latest('fecha_inicio')
            ->first();

        if ($historialActual) {
            $historialActual->update(['fecha_fin' => now()]);
        }

        HistorialEstadoDespacho::create([
            'id_despacho' => $id,
            'id_estado_despacho' => $nuevoEstado,
            'fecha_inicio' => now(),
        ]);

        $despacho->update(['id_estado_despacho' => $nuevoEstado]);

        AuditService::log(auth()->id(), 'cambiar-estado', 'despachos', $despacho->id_despacho);

        return $this->jsonResponse(
            $despacho->load('estado', 'historiales.estado'),
            'Estado del despacho actualizado exitosamente.'
        );
    }
}
