<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\ApiController;
use App\Models\DetallePedido;
use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Líneas de detalle de un pedido.
 *
 * El front-end (public/js/modules/pedidos.js → saveDetalle) manda el alta,
 * modificación y baja de cada línea por separado al guardar un pedido. Sin estas
 * rutas el pedido se creaba sin líneas y el guardado devolvía 404.
 */
class DetallePedidoController extends ApiController
{
    public function index($pedido): JsonResponse
    {
        $detalles = DetallePedido::with('producto')
            ->where('id_pedido', $pedido)
            ->orderBy('id_detalle')
            ->get();

        return $this->jsonResponse($detalles);
    }

    public function store(Request $request, $pedido): JsonResponse
    {
        $pedidoModel = Pedido::findOrFail($pedido);

        $validated = $request->validate([
            'id_producto'           => 'required|integer|exists:productos,id_producto',
            'cantidad_solicitada'   => 'required|numeric|min:0.01',
            'precio_unitario'       => 'required|numeric|min:0',
        ]);

        $detalle = new DetallePedido($validated);
        $detalle->id_pedido = $pedidoModel->id_pedido;
        $detalle->subtotal = $this->calcularSubtotal($detalle->cantidad_solicitada, $detalle->precio_unitario);
        $detalle->save();

        return $this->jsonResponse($detalle->load('producto'), 'Detalle agregado al pedido.', 201);
    }

    public function update(Request $request, $pedido, $detalle): JsonResponse
    {
        $linea = DetallePedido::where('id_pedido', $pedido)
            ->where('id_detalle', $detalle)
            ->firstOrFail();

        $validated = $request->validate([
            'id_producto'         => 'sometimes|integer|exists:productos,id_producto',
            'cantidad_solicitada' => 'sometimes|numeric|min:0.01',
            'precio_unitario'     => 'sometimes|numeric|min:0',
        ]);

        $linea->fill($validated);
        $linea->subtotal = $this->calcularSubtotal($linea->cantidad_solicitada, $linea->precio_unitario);
        $linea->save();

        return $this->jsonResponse($linea->load('producto'), 'Detalle actualizado.');
    }

    public function destroy($pedido, $detalle): JsonResponse
    {
        $linea = DetallePedido::where('id_pedido', $pedido)
            ->where('id_detalle', $detalle)
            ->firstOrFail();

        $linea->delete();

        return $this->jsonResponse(null, 'Detalle eliminado.');
    }

    private function calcularSubtotal($cantidad, $precio): float
    {
        return round((float) $cantidad * (float) $precio, 2);
    }
}
