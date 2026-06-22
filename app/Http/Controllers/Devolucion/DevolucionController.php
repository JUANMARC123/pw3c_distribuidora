<?php

namespace App\Http\Controllers\Devolucion;

use App\Http\Controllers\ApiController;
use App\Models\Devolucion\Devolucion;
use App\Models\Devolucion\HistorialEstadoDevolucion;
use App\Models\Inventario\Inventario;
use App\Models\Inventario\MovimientoInventario;
use App\Models\Inventario\TipoMovimiento;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class DevolucionController extends ApiController
{
    private const TRANSICIONES_DEVOLUCION = [
        1 => [2, 3],
        2 => [4],
        3 => [],
        4 => [],
    ];

    public function index(Request $request)
    {
        $query = Devolucion::with('pedido', 'usuario', 'tipoDevolucion', 'estado');

        if ($request->filled('id_estado_devolucion')) {
            $query->where('id_estado_devolucion', $request->id_estado_devolucion);
        }

        if ($request->filled('id_pedido')) {
            $query->where('id_pedido', $request->id_pedido);
        }

        if ($request->filled('id_tipo_devolucion')) {
            $query->where('id_tipo_devolucion', $request->id_tipo_devolucion);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_devolucion', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_devolucion', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        return $this->paginatedResponse(
            $query->orderBy('fecha_devolucion', 'desc')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_pedido' => 'required|integer|exists:pedidos,id_pedido',
            'id_tipo_devolucion' => 'required|integer|exists:tipos_devolucion,id_tipo_devolucion',
            'id_estado_devolucion' => 'required|integer|exists:estados_devolucion,id_estado_devolucion',
            'motivo' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'required|integer|exists:productos,id_producto',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.motivo_detalle' => 'nullable|string',
        ]);

        $detalles = $data['detalles'];
        unset($data['detalles']);

        // RN-079: No devolver más cantidad de la vendida
        $pedido = \App\Models\Pedido\Pedido::with('detalles')->find($data['id_pedido']);
        if (!$pedido) {
            return $this->errorResponse('El pedido no existe.', 404);
        }
        foreach ($detalles as $detalle) {
            $productoId = $detalle['id_producto'];
            $cantidadPedido = $pedido->detalles->where('id_producto', $productoId)->sum('cantidad');
            if ($cantidadPedido <= 0) {
                return $this->errorResponse(
                    "El producto ID {$productoId} no está en el pedido original.", 422
                );
            }
            $totalDevuelto = Devolucion::where('id_pedido', $data['id_pedido'])
                ->where('id_estado_devolucion', '!=', 3)
                ->whereHas('detalles', function ($q) use ($productoId) {
                    $q->where('id_producto', $productoId);
                })
                ->join('detalles_devolucion', 'devoluciones.id_devolucion', '=', 'detalles_devolucion.id_devolucion')
                ->where('detalles_devolucion.id_producto', $productoId)
                ->sum('detalles_devolucion.cantidad');
            if (($totalDevuelto + $detalle['cantidad']) > $cantidadPedido) {
                return $this->errorResponse(
                    "La cantidad a devolver del producto ID {$productoId} excede la cantidad vendida ({$cantidadPedido}).", 422
                );
            }
        }

        $data['id_usuario'] = auth()->id();
        $data['fecha_devolucion'] = now();

        $devolucion = Devolucion::create($data);

        foreach ($detalles as $detalle) {
            $detalle['id_devolucion'] = $devolucion->id_devolucion;
            $detalle['subtotal'] = round($detalle['cantidad'] * $detalle['precio_unitario'], 2);
            $devolucion->detalles()->create($detalle);
        }

        AuditService::log(auth()->id(), 'crear', 'devoluciones', $devolucion->id_devolucion);

        HistorialEstadoDevolucion::create([
            'id_devolucion' => $devolucion->id_devolucion,
            'id_estado_devolucion' => $data['id_estado_devolucion'],
            'fecha_inicio' => now(),
        ]);

        return $this->jsonResponse(
            $devolucion->load('pedido', 'usuario', 'tipoDevolucion', 'estado', 'detalles.producto'),
            'Devolución creada exitosamente.',
            201
        );
    }

    public function show($id)
    {
        $devolucion = Devolucion::with([
            'pedido',
            'usuario',
            'tipoDevolucion',
            'estado',
            'detalles.producto',
            'historiales.estado',
        ])->findOrFail($id);

        return $this->jsonResponse($devolucion);
    }

    public function update(Request $request, $id)
    {
        $devolucion = Devolucion::findOrFail($id);

        $data = $request->validate([
            'id_tipo_devolucion' => 'sometimes|integer|exists:tipos_devolucion,id_tipo_devolucion',
            'motivo' => 'nullable|string',
            'detalles' => 'sometimes|array|min:1',
            'detalles.*.id_producto' => 'required|integer|exists:productos,id_producto',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.motivo_detalle' => 'nullable|string',
        ]);

        $devolucion->update($data);

        if ($request->has('detalles')) {
            $devolucion->detalles()->delete();
            foreach ($data['detalles'] as $detalle) {
                $detalle['id_devolucion'] = $devolucion->id_devolucion;
                $detalle['subtotal'] = round($detalle['cantidad'] * $detalle['precio_unitario'], 2);
                $devolucion->detalles()->create($detalle);
            }
        }

        AuditService::log(auth()->id(), 'editar', 'devoluciones', $devolucion->id_devolucion);

        return $this->jsonResponse(
            $devolucion->load('pedido', 'usuario', 'tipoDevolucion', 'estado', 'detalles.producto'),
            'Devolución actualizada exitosamente.'
        );
    }

    public function destroy($id)
    {
        $devolucion = Devolucion::findOrFail($id);

        try {
            $devolucion->delete();
            AuditService::log(auth()->id(), 'eliminar', 'devoluciones', $id);
            return $this->jsonResponse(null, 'Devolución eliminada exitosamente.');
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                return $this->errorResponse('No se puede eliminar esta devolución porque tiene registros asociados.', 409);
            }
            throw $e;
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'id_estado_devolucion' => 'required|integer|exists:estados_devolucion,id_estado_devolucion',
        ]);

        $devolucion = Devolucion::findOrFail($id);
        $nuevoEstado = (int) $request->id_estado_devolucion;
        $estadoActual = (int) $devolucion->id_estado_devolucion;

        $transicionesValidas = self::TRANSICIONES_DEVOLUCION[$estadoActual] ?? [];
        if (!empty($transicionesValidas) && !in_array($nuevoEstado, $transicionesValidas)) {
            return $this->errorResponse(
                "Transición de estado no válida. No se puede cambiar de " .
                "{$devolucion->estado->nombre_estado} al estado solicitado.",
                422
            );
        }

        if ($nuevoEstado === $estadoActual) {
            return $this->errorResponse('La devolución ya se encuentra en este estado.', 422);
        }

        $historialActual = HistorialEstadoDevolucion::where('id_devolucion', $id)
            ->whereNull('fecha_fin')
            ->latest('fecha_inicio')
            ->first();

        if ($historialActual) {
            $historialActual->update(['fecha_fin' => now()]);
        }

        HistorialEstadoDevolucion::create([
            'id_devolucion' => $id,
            'id_estado_devolucion' => $nuevoEstado,
            'fecha_inicio' => now(),
        ]);

        $devolucion->update(['id_estado_devolucion' => $nuevoEstado]);

        // RN-080: Devolución "Completada" (estado 4) aumenta stock automáticamente
        if ($nuevoEstado === 4) {
            DB::transaction(function () use ($devolucion) {
                $tipoEntrada = TipoMovimiento::where('nombre_tipo', 'Entrada')->first();
                if (!$tipoEntrada) return;

                $devolucion->load('detalles');
                foreach ($devolucion->detalles as $detalle) {
                    $inventario = Inventario::firstOrCreate(
                        ['id_producto' => $detalle->id_producto, 'id_lote' => null],
                        ['stock_actual' => 0, 'stock_minimo' => 0]
                    );

                    $stockAnterior = $inventario->stock_actual;
                    $nuevoStock = $stockAnterior + $detalle->cantidad;

                    MovimientoInventario::create([
                        'id_inventario' => $inventario->id_inventario,
                        'id_tipo_movimiento' => $tipoEntrada->id_tipo_movimiento,
                        'id_usuario' => auth()->id(),
                        'cantidad' => $detalle->cantidad,
                        'stock_anterior' => $stockAnterior,
                        'stock_posterior' => $nuevoStock,
                        'referencia' => 'DEV-' . $devolucion->id_devolucion,
                        'observaciones' => 'Devolución completada automáticamente',
                        'created_at' => now(),
                    ]);

                    $inventario->update(['stock_actual' => $nuevoStock]);
                }
            });
        }

        AuditService::log(auth()->id(), 'cambiar-estado', 'devoluciones', $devolucion->id_devolucion);

        return $this->jsonResponse(
            $devolucion->load('estado', 'historiales.estado'),
            'Estado de la devolución actualizado exitosamente.'
        );
    }
}
