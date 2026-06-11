<?php

namespace App\Http\Controllers\Pedido;

use App\Http\Controllers\ApiController;
use App\Models\Pedido\Pedido;
use App\Models\Pedido\HistorialEstadoPedido;
use Illuminate\Http\Request;

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
            'id_usuario' => 'required|integer|exists:usuarios,id_usuario',
            'id_estado_pedido' => 'required|integer|exists:estados_pedido,id_estado_pedido',
            'observaciones' => 'nullable|string',
        ]);

        $data['fecha_pedido'] = now();

        $pedido = Pedido::create($data);

        HistorialEstadoPedido::create([
            'id_pedido' => $pedido->id_pedido,
            'id_estado_pedido' => $data['id_estado_pedido'],
            'fecha_inicio' => now(),
        ]);

        return $this->jsonResponse(
            $pedido->load('farmacia', 'usuario', 'estado'),
            'Pedido creado exitosamente.',
            201
        );
    }

    public function show($id)
    {
        $pedido = Pedido::with([
            'farmacia',
            'usuario',
            'estado',
            'historiales.estado',
            'despacho',
        ])->findOrFail($id);

        return $this->jsonResponse($pedido);
    }

    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $data = $request->validate([
            'id_farmacia' => 'sometimes|integer|exists:farmacias,id_farmacia',
            'observaciones' => 'nullable|string',
        ]);

        $pedido->update($data);

        return $this->jsonResponse(
            $pedido->load('farmacia', 'usuario', 'estado'),
            'Pedido actualizado exitosamente.'
        );
    }

    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->delete();

        return $this->jsonResponse(null, 'Pedido eliminado exitosamente.');
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'id_estado_pedido' => 'required|integer|exists:estados_pedido,id_estado_pedido',
        ]);

        $pedido = Pedido::findOrFail($id);

        $historialActual = HistorialEstadoPedido::where('id_pedido', $id)
            ->whereNull('fecha_fin')
            ->latest('fecha_inicio')
            ->first();

        if ($historialActual) {
            $historialActual->update(['fecha_fin' => now()]);
        }

        HistorialEstadoPedido::create([
            'id_pedido' => $id,
            'id_estado_pedido' => $request->id_estado_pedido,
            'fecha_inicio' => now(),
        ]);

        $pedido->update(['id_estado_pedido' => $request->id_estado_pedido]);

        return $this->jsonResponse(
            $pedido->load('estado', 'historiales.estado'),
            'Estado del pedido actualizado exitosamente.'
        );
    }
}
