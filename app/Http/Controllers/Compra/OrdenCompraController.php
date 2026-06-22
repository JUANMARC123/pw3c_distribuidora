<?php

namespace App\Http\Controllers\Compra;

use App\Http\Controllers\ApiController;
use App\Models\Compra\OrdenCompra;
use App\Models\Compra\DetalleCompra;
use App\Models\Inventario\Inventario;
use App\Models\Inventario\MovimientoInventario;
use App\Models\Inventario\TipoMovimiento;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class OrdenCompraController extends ApiController
{
    private const TRANSICIONES_ORDEN = [
        1 => [2, 5],
        2 => [3, 5],
        3 => [4, 5],
        4 => [],
        5 => [],
    ];

    public function index(Request $request)
    {
        $query = OrdenCompra::with('proveedor', 'usuario', 'estado');

        if ($request->filled('id_estado_orden_compra')) {
            $query->where('id_estado_orden_compra', $request->id_estado_orden_compra);
        }

        if ($request->filled('id_proveedor')) {
            $query->where('id_proveedor', $request->id_proveedor);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('codigo_orden', 'like', "%{$search}%");
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_orden', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_orden', '<=', $request->fecha_hasta);
        }

        return $this->paginatedResponse(
            $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo_orden' => 'required|string|max:50|unique:ordenes_compra,codigo_orden',
            'id_proveedor' => 'required|integer|exists:proveedores,id_proveedor',
            'id_estado_orden_compra' => 'required|integer|exists:estados_orden_compra,id_estado_orden_compra',
            'fecha_estimada_recibido' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'required|integer|exists:productos,id_producto',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        $orden = OrdenCompra::create([
            'codigo_orden' => $data['codigo_orden'],
            'id_proveedor' => $data['id_proveedor'],
            'id_usuario' => auth()->id(),
            'id_estado_orden_compra' => $data['id_estado_orden_compra'],
            'fecha_orden' => now(),
            'fecha_estimada_recibido' => $data['fecha_estimada_recibido'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        foreach ($data['detalles'] as $detalle) {
            $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];
            DetalleCompra::create([
                'id_orden_compra' => $orden->id_orden_compra,
                'id_producto' => $detalle['id_producto'],
                'cantidad' => $detalle['cantidad'],
                'precio_unitario' => $detalle['precio_unitario'],
                'subtotal' => $subtotal,
            ]);
        }

        AuditService::log(auth()->id(), 'crear', 'ordenes_compra', $orden->id_orden_compra);

        return $this->jsonResponse(
            $orden->load('proveedor', 'usuario', 'estado', 'detalles.producto'),
            'Orden de compra creada exitosamente.',
            201
        );
    }

    public function show($id)
    {
        $orden = OrdenCompra::with([
            'proveedor',
            'usuario',
            'estado',
            'detalles.producto.categoria',
            'detalles.producto.laboratorio',
        ])->findOrFail($id);

        return $this->jsonResponse($orden);
    }

    public function update(Request $request, $id)
    {
        $orden = OrdenCompra::findOrFail($id);

        $data = $request->validate([
            'codigo_orden' => 'sometimes|string|max:50|unique:ordenes_compra,codigo_orden,' . $id . ',id_orden_compra',
            'id_proveedor' => 'sometimes|integer|exists:proveedores,id_proveedor',
            'fecha_estimada_recibido' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'detalles' => 'sometimes|array|min:1',
            'detalles.*.id_producto' => 'required_with:detalles|integer|exists:productos,id_producto',
            'detalles.*.cantidad' => 'required_with:detalles|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required_with:detalles|numeric|min:0',
        ]);

        $orden->update($data);

        if ($request->has('detalles')) {
            $orden->detalles()->delete();

            foreach ($data['detalles'] as $detalle) {
                $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];
                DetalleCompra::create([
                    'id_orden_compra' => $orden->id_orden_compra,
                    'id_producto' => $detalle['id_producto'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $subtotal,
                ]);
            }
        }

        AuditService::log(auth()->id(), 'editar', 'ordenes_compra', $orden->id_orden_compra);

        return $this->jsonResponse(
            $orden->load('proveedor', 'usuario', 'estado', 'detalles.producto'),
            'Orden de compra actualizada exitosamente.'
        );
    }

    public function destroy($id)
    {
        $orden = OrdenCompra::findOrFail($id);

        try {
            $orden->detalles()->delete();
            $orden->delete();

            AuditService::log(auth()->id(), 'eliminar', 'ordenes_compra', $id);

            return $this->jsonResponse(null, 'Orden de compra eliminada exitosamente.');
        } catch (QueryException $e) {
            throw $e;
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'id_estado_orden_compra' => 'required|integer|exists:estados_orden_compra,id_estado_orden_compra',
        ]);

        $orden = OrdenCompra::findOrFail($id);
        $nuevoEstado = (int) $request->id_estado_orden_compra;
        $estadoActual = (int) $orden->id_estado_orden_compra;

        $transicionesValidas = self::TRANSICIONES_ORDEN[$estadoActual] ?? [];
        if (!empty($transicionesValidas) && !in_array($nuevoEstado, $transicionesValidas)) {
            return $this->errorResponse(
                "Transición de estado no válida.",
                422
            );
        }

        if ($nuevoEstado === $estadoActual) {
            return $this->errorResponse('La orden ya se encuentra en este estado.', 422);
        }

        // RN-093: Recepción de compra (estado 4 = Recibida) genera entrada en inventario
        if ($nuevoEstado === 4) {
            DB::transaction(function () use ($orden) {
                $tipoEntrada = TipoMovimiento::where('nombre_tipo', 'Entrada')->first();
                if (!$tipoEntrada) return;

                $orden->load('detalles.producto');
                foreach ($orden->detalles as $detalle) {
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
                        'referencia' => 'OC-' . $orden->codigo_orden,
                        'observaciones' => 'Recepción automática de orden de compra',
                        'created_at' => now(),
                    ]);

                    $inventario->update(['stock_actual' => $nuevoStock]);
                }
            });
        }

        $orden->update(['id_estado_orden_compra' => $nuevoEstado]);

        AuditService::log(auth()->id(), 'cambiar-estado', 'ordenes_compra', $orden->id_orden_compra);

        return $this->jsonResponse(
            $orden->load('estado'),
            'Estado de la orden actualizado exitosamente.'
        );
    }
}
