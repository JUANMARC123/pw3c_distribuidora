<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\ApiController;
use App\Models\Pedido\Pedido;
use App\Models\Pedido\HistorialEstadoPedido;
use App\Models\Farmacia\Farmacia;
use App\Models\Farmacia\ContactoFarmacia;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Services\AuditService;

class PedidoController extends ApiController
{
    public function index(Request $request)
    {
        $query = Pedido::with('farmacia', 'usuario', 'estado');

        if ($request->filled('id_estado_pedido')) {
            $query->where('id_estado_pedido', $request->id_estado_pedido);
        }

        if ($request->filled('id_farmacia')) {
            $query->where('id_farmacia', $request->id_farmacia);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_pedido', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_pedido', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        return $this->paginatedResponse(
            $query->orderBy('fecha_pedido', 'desc')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_farmacia' => 'required|integer|exists:farmacias,id_farmacia',
            'id_contacto' => 'nullable|integer|exists:contactos_farmacia,id_contacto',
            'id_usuario' => 'required|integer|exists:usuarios,id_usuario',
            'id_estado_pedido' => 'required|integer|exists:estados_pedido,id_estado_pedido',
            'observaciones' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'required|integer|exists:productos,id_producto',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        // RN-021: Farmacia cerrada/inactiva no recibe pedidos
        $farmacia = Farmacia::findOrFail($data['id_farmacia']);
        if ($farmacia->id_estado_farmacia !== 1) {
            return $this->errorResponse('La farmacia seleccionada no está activa y no puede recibir pedidos.', 422);
        }

        // RN-063: Contacto debe pertenecer a la misma farmacia del pedido
        if (!empty($data['id_contacto'])) {
            $contacto = ContactoFarmacia::find($data['id_contacto']);
            if (!$contacto || $contacto->id_farmacia !== (int)$data['id_farmacia']) {
                return $this->errorResponse('El contacto seleccionado no pertenece a la farmacia del pedido.', 422);
            }
        }

        $data['fecha_pedido'] = now();

        $detalles = $data['detalles'];
        unset($data['detalles']);

        $pedido = Pedido::create($data);

        foreach ($detalles as $detalle) {
            $detalle['id_pedido'] = $pedido->id_pedido;
            $detalle['subtotal'] = round($detalle['cantidad'] * $detalle['precio_unitario'], 2);
            $pedido->detalles()->create($detalle);
        }

        AuditService::log(auth()->id(), 'crear', 'pedidos', $pedido->id_pedido);

        HistorialEstadoPedido::create([
            'id_pedido' => $pedido->id_pedido,
            'id_estado_pedido' => $data['id_estado_pedido'],
            'fecha_inicio' => now(),
        ]);

        return $this->jsonResponse(
            $pedido->load('farmacia', 'contacto', 'usuario', 'estado', 'detalles.producto'),
            'Pedido creado exitosamente.',
            201
        );
    }

    public function show($id)
    {
        $pedido = Pedido::with([
            'farmacia',
            'contacto',
            'usuario',
            'estado',
            'historiales.estado',
            'despacho',
            'detalles.producto',
        ])->findOrFail($id);

        return $this->jsonResponse($pedido);
    }

    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $data = $request->validate([
            'id_farmacia' => 'sometimes|integer|exists:farmacias,id_farmacia',
            'id_contacto' => 'nullable|integer|exists:contactos_farmacia,id_contacto',
            'observaciones' => 'nullable|string',
            'detalles' => 'sometimes|array|min:1',
            'detalles.*.id_producto' => 'required|integer|exists:productos,id_producto',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        // RN-063: Contacto debe pertenecer a la misma farmacia del pedido
        if (!empty($data['id_contacto'])) {
            $idFarmacia = $data['id_farmacia'] ?? $pedido->id_farmacia;
            $contacto = ContactoFarmacia::find($data['id_contacto']);
            if (!$contacto || $contacto->id_farmacia !== (int)$idFarmacia) {
                return $this->errorResponse('El contacto seleccionado no pertenece a la farmacia del pedido.', 422);
            }
        }

        $pedido->update($data);

        if ($request->has('detalles')) {
            $pedido->detalles()->delete();
            foreach ($data['detalles'] as $detalle) {
                $detalle['id_pedido'] = $pedido->id_pedido;
                $detalle['subtotal'] = round($detalle['cantidad'] * $detalle['precio_unitario'], 2);
                $pedido->detalles()->create($detalle);
            }
        }

        AuditService::log(auth()->id(), 'editar', 'pedidos', $pedido->id_pedido);

        return $this->jsonResponse(
            $pedido->load('farmacia', 'usuario', 'estado', 'detalles.producto'),
            'Pedido actualizado exitosamente.'
        );
    }

   public function destroy($id)
{
    $pedido = Pedido::findOrFail($id);

    try {
        $pedido->delete();

        AuditService::log(auth()->id(), 'eliminar', 'pedidos', $id);

        return $this->jsonResponse(null, 'Pedido eliminado exitosamente.');
    } catch (QueryException $e) {
        if ($e->getCode() == 23000) {
            return response()->json([
                'success' => false,
                'has_dispatch' => true,
                'message' => 'No se puede eliminar este pedido porque tiene un despacho registrado.'
            ], 409);
        }

        throw $e;
    }
}

    private const TRANSICIONES_PEDIDO = [
        1 => [2, 6],
        2 => [3, 6],
        3 => [4, 6],
        4 => [5],
        5 => [],
        6 => [],
    ];

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'id_estado_pedido' => 'required|integer|exists:estados_pedido,id_estado_pedido',
        ]);

        $pedido = Pedido::findOrFail($id);
        $nuevoEstado = (int) $request->id_estado_pedido;
        $estadoActual = (int) $pedido->id_estado_pedido;

        $transicionesValidas = self::TRANSICIONES_PEDIDO[$estadoActual] ?? [];
        if (!empty($transicionesValidas) && !in_array($nuevoEstado, $transicionesValidas)) {
            return $this->errorResponse(
                "Transición de estado no válida. No se puede cambiar de " .
                "{$pedido->estado->nombre_estado} al estado solicitado.",
                422
            );
        }

        if ($nuevoEstado === $estadoActual) {
            return $this->errorResponse('El pedido ya se encuentra en este estado.', 422);
        }

        $historialActual = HistorialEstadoPedido::where('id_pedido', $id)
            ->whereNull('fecha_fin')
            ->latest('fecha_inicio')
            ->first();

        if ($historialActual) {
            $historialActual->update(['fecha_fin' => now()]);
        }

        HistorialEstadoPedido::create([
            'id_pedido' => $id,
            'id_estado_pedido' => $nuevoEstado,
            'fecha_inicio' => now(),
        ]);

        $pedido->update(['id_estado_pedido' => $nuevoEstado]);

        AuditService::log(auth()->id(), 'cambiar-estado', 'pedidos', $pedido->id_pedido);

        return $this->jsonResponse(
            $pedido->load('estado', 'historiales.estado'),
            'Estado del pedido actualizado exitosamente.'
        );
    }
}
