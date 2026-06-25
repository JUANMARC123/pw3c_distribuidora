<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\ApiController;
use App\Models\Pedido\Pedido;
use App\Models\Pedido\DetallePedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetallePedidoController extends ApiController
{
    public function index($pedidoId)
    {
        $pedido = Pedido::findOrFail($pedidoId);

        $detalles = DetallePedido::with('producto', 'estado')
            ->where('id_pedido', $pedidoId)
            ->get();

        return $this->jsonResponse($detalles);
    }

    public function store(Request $request, $pedidoId)
    {
        $pedido = Pedido::findOrFail($pedidoId);

        $data = $request->validate([
            'id_producto' => 'required|integer|exists:productos,id_producto',
            'cantidad_solicitada' => 'required|numeric|min:0.01',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $data['id_pedido'] = $pedidoId;
        $data['cantidad_entregada'] = 0;
        $data['subtotal'] = $data['cantidad_solicitada'] * $data['precio_unitario'];
        $data['id_estado_detalle'] = 1;

        DB::transaction(function () use ($pedido, $data) {
            DetallePedido::create($data);
            $this->recalcularPedido($pedido);
        });

        return $this->jsonResponse(
            DetallePedido::with('producto', 'estado')->where('id_pedido', $pedidoId)->get(),
            'Producto agregado al pedido.',
            201
        );
    }

    public function update(Request $request, $pedidoId, $detalleId)
    {
        $pedido = Pedido::findOrFail($pedidoId);
        $detalle = DetallePedido::where('id_pedido', $pedidoId)
            ->findOrFail($detalleId);

        $data = $request->validate([
            'cantidad_solicitada' => 'sometimes|required|numeric|min:0',
            'cantidad_entregada' => 'sometimes|required|numeric|min:0',
            'precio_unitario' => 'sometimes|required|numeric|min:0',
            'id_estado_detalle' => 'sometimes|integer|exists:estados_detalle,id_estado_detalle',
        ]);

        DB::transaction(function () use ($pedido, $detalle, $data) {
            if (isset($data['cantidad_solicitada'])) {
                $data['subtotal'] = $data['cantidad_solicitada'] * ($data['precio_unitario'] ?? $detalle->precio_unitario);
            }
            if (isset($data['cantidad_entregada']) && !isset($data['precio_unitario'])) {
                $data['subtotal'] = $detalle->cantidad_solicitada * $detalle->precio_unitario;
            }
            if (isset($data['cantidad_solicitada']) && isset($data['precio_unitario'])) {
                $data['subtotal'] = $data['cantidad_solicitada'] * $data['precio_unitario'];
            }

            $detalle->update($data);
            $this->recalcularPedido($pedido);
        });

        return $this->jsonResponse(
            DetallePedido::with('producto', 'estado')->where('id_pedido', $pedidoId)->get(),
            'Detalle actualizado.'
        );
    }

    public function destroy($pedidoId, $detalleId)
    {
        $pedido = Pedido::findOrFail($pedidoId);
        $detalle = DetallePedido::where('id_pedido', $pedidoId)
            ->findOrFail($detalleId);

        DB::transaction(function () use ($pedido, $detalle) {
            $detalle->delete();
            $this->recalcularPedido($pedido);
        });

        return $this->jsonResponse(null, 'Producto eliminado del pedido.');
    }

    private function recalcularPedido(Pedido $pedido)
    {
        $totales = DetallePedido::where('id_pedido', $pedido->id_pedido)
            ->selectRaw('COALESCE(SUM(subtotal), 0) as subtotal')
            ->first();

        $subtotal = $totales->subtotal;
        $descuento = $pedido->descuento ?? 0;
        $total = $subtotal - $descuento;

        $pedido->update([
            'subtotal' => $subtotal,
            'total' => $total,
        ]);
    }

    public function actualizarEntrega(Request $request, $pedidoId, $detalleId)
    {
        $pedido = Pedido::findOrFail($pedidoId);
        $detalle = DetallePedido::where('id_pedido', $pedidoId)
            ->findOrFail($detalleId);

        $data = $request->validate([
            'cantidad_entregada' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($detalle, $data) {
            $detalle->update(['cantidad_entregada' => $data['cantidad_entregada']]);

            if ($data['cantidad_entregada'] >= $detalle->cantidad_solicitada) {
                $detalle->update(['id_estado_detalle' => 4]);
            } elseif ($data['cantidad_entregada'] > 0) {
                $detalle->update(['id_estado_detalle' => 5]);
            }
        });

        return $this->jsonResponse(
            DetallePedido::with('producto', 'estado')->where('id_pedido', $pedidoId)->get(),
            'Entrega actualizada.'
        );
    }
}
