<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\ApiController;
use App\Models\DetallePedido;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetallePedidoController extends ApiController
{
    public function index($pedidoId)
    {
        $pedido = Pedido::findOrFail($pedidoId);
        $detalles = DetallePedido::with('producto')
            ->where('id_pedido', $pedidoId)
            ->get();

        return $this->jsonResponse($detalles);
    }

    public function store(Request $request, $pedidoId)
    {
        $pedido = Pedido::findOrFail($pedidoId);

        $validated = $request->validate([
            'id_producto' => 'required|integer|exists:productos,id_producto',
            'cantidad_solicitada' => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $validated['id_pedido'] = $pedidoId;
        $validated['subtotal'] = $validated['cantidad_solicitada'] * $validated['precio_unitario'];
        $validated['cantidad_entregada'] = 0;
        $validated['cantidad_rechazada'] = 0;

        $detalle = DetallePedido::create($validated);

        $this->recalcularPedido($pedidoId);

        return $this->jsonResponse($detalle->load('producto'), 'Detalle agregado exitosamente.', 201);
    }

    public function update(Request $request, $pedidoId, $id)
    {
        $detalle = DetallePedido::where('id_pedido', $pedidoId)->findOrFail($id);

        $validated = $request->validate([
            'id_producto' => 'sometimes|integer|exists:productos,id_producto',
            'cantidad_solicitada' => 'sometimes|integer|min:0',
            'cantidad_entregada' => 'sometimes|integer|min:0',
            'cantidad_rechazada' => 'sometimes|integer|min:0',
            'motivo_rechazo' => 'nullable|string|max:500',
            'precio_unitario' => 'sometimes|numeric|min:0',
        ]);

        if (isset($validated['cantidad_solicitada']) || isset($validated['precio_unitario'])) {
            $qty = $validated['cantidad_solicitada'] ?? $detalle->cantidad_solicitada;
            $price = $validated['precio_unitario'] ?? $detalle->precio_unitario;
            $validated['subtotal'] = $qty * $price;
        }

        $detalle->update($validated);

        $this->recalcularPedido($pedidoId);

        return $this->jsonResponse($detalle->load('producto'), 'Detalle actualizado exitosamente.');
    }

    public function destroy($pedidoId, $id)
    {
        $detalle = DetallePedido::where('id_pedido', $pedidoId)->findOrFail($id);
        $detalle->delete();

        $this->recalcularPedido($pedidoId);

        return $this->jsonResponse(null, 'Detalle eliminado exitosamente.');
    }

    public function actualizarEntrega(Request $request, $pedidoId, $id)
    {
        $detalle = DetallePedido::where('id_pedido', $pedidoId)->findOrFail($id);

        $validated = $request->validate([
            'cantidad_entregada' => 'required|integer|min:0',
            'cantidad_rechazada' => 'nullable|integer|min:0',
            'motivo_rechazo' => 'nullable|string|max:500',
        ]);

        $detalle->update($validated);

        $this->recalcularPedido($pedidoId);

        return $this->jsonResponse($detalle->load('producto'), 'Entrega actualizada.');
    }

    private function recalcularPedido($pedidoId)
    {
        $pedido = Pedido::find($pedidoId);
        if (!$pedido) return;

        $subtotal = DetallePedido::where('id_pedido', $pedidoId)
            ->sum('subtotal');

        $descuento = $pedido->descuento ?? 0;
        $total = $subtotal - $descuento;

        $pedido->update([
            'subtotal' => $subtotal,
            'total' => $total,
        ]);
    }
}
