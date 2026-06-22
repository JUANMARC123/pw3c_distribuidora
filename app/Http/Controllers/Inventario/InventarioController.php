<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\ApiController;
use App\Models\Inventario\Inventario;
use App\Models\Inventario\MovimientoInventario;
use App\Models\Inventario\TipoMovimiento;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends ApiController
{
    public function index(Request $request)
    {
        $query = Inventario::with('producto', 'lote', 'producto.categoria', 'producto.laboratorio', 'ubicacion.almacen');

        if ($request->filled('id_producto')) {
            $query->where('id_producto', $request->id_producto);
        }

        if ($request->filled('alertas')) {
            $query->whereColumn('stock_actual', '<=', 'stock_minimo');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('producto', function ($q) use ($search) {
                $q->where('nombre_producto', 'like', "%{$search}%")
                  ->orWhere('codigo_producto', 'like', "%{$search}%");
            });
        }

        return $this->paginatedResponse(
            $query->orderBy('stock_actual', 'asc')->paginate($request->per_page ?? 15)
        );
    }

    public function show($id)
    {
        $inventario = Inventario::with([
            'producto.categoria',
            'producto.laboratorio',
            'producto.presentacion',
            'producto.unidadMedida',
            'lote',
            'movimientos.tipoMovimiento',
            'movimientos.usuario',
            'ubicacion.almacen',
        ])->findOrFail($id);

        return $this->jsonResponse($inventario);
    }

    public function update(Request $request, $id)
    {
        $inventario = Inventario::findOrFail($id);

        $data = $request->validate([
            'id_ubicacion' => 'nullable|integer|exists:ubicaciones_almacen,id_ubicacion',
            'precio_venta' => 'nullable|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
        ]);

        if (isset($data['precio_venta']) || isset($data['id_ubicacion'])) {
            $data['fecha_actualizacion'] = now();
        }

        $inventario->update($data);

        AuditService::log(auth()->id(), 'editar', 'inventario', $id);

        return $this->jsonResponse(
            $inventario->load('producto', 'lote', 'ubicacion.almacen'),
            'Inventario actualizado exitosamente.'
        );
    }

    public function movimientos(Request $request, $id)
    {
        $inventario = Inventario::findOrFail($id);

        $query = MovimientoInventario::with('tipoMovimiento', 'usuario')
            ->where('id_inventario', $id);

        if ($request->filled('fecha_desde')) {
            $query->where('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('created_at', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        return $this->paginatedResponse(
            $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15)
        );
    }

    public function alertas()
    {
        $items = Inventario::with('producto', 'lote')
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderByRaw('(stock_actual / stock_minimo) asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id_inventario' => $item->id_inventario,
                    'producto' => $item->producto->nombre_producto ?? '—',
                    'codigo' => $item->producto->codigo_producto ?? '—',
                    'lote' => $item->lote->codigo_lote ?? '—',
                    'stock_actual' => $item->stock_actual,
                    'stock_minimo' => $item->stock_minimo,
                    'diferencia' => $item->stock_minimo - $item->stock_actual,
                ];
            });

        return $this->jsonResponse([
            'total_alertas' => $items->count(),
            'items' => $items,
        ]);
    }

    public function storeMovimiento(Request $request)
    {
        $data = $request->validate([
            'id_producto' => 'required|integer|exists:productos,id_producto',
            'id_lote' => 'nullable|integer|exists:lotes,id_lote',
            'id_tipo_movimiento' => 'required|integer|exists:tipos_movimiento,id_tipo_movimiento',
            'cantidad' => 'required|numeric|min:0',
            'id_ubicacion' => 'nullable|integer|exists:ubicaciones_almacen,id_ubicacion',
            'precio_venta' => 'nullable|numeric|min:0',
            'referencia' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ]);

        $tipoMovimiento = TipoMovimiento::findOrFail($data['id_tipo_movimiento']);
        $esEntrada = in_array(strtolower($tipoMovimiento->nombre_tipo), ['entrada', 'ajuste (+)', 'ajuste positivo']);

        return DB::transaction(function () use ($data, $esEntrada) {
            $inventario = Inventario::firstOrCreate(
                [
                    'id_producto' => $data['id_producto'],
                    'id_lote' => $data['id_lote'],
                ],
                [
                    'stock_actual' => 0,
                    'stock_minimo' => 0,
                ]
            );

            $updateData = [];
            if (isset($data['id_ubicacion'])) {
                $updateData['id_ubicacion'] = $data['id_ubicacion'];
            }
            if (isset($data['precio_venta'])) {
                $updateData['precio_venta'] = $data['precio_venta'];
            }
            if (!empty($updateData)) {
                $updateData['fecha_actualizacion'] = now();
                $inventario->update($updateData);
            }

            $stockAnterior = $inventario->stock_actual;
            $cantidad = $data['cantidad'];

            if ($esEntrada) {
                $stockPosterior = $stockAnterior + $cantidad;
            } else {
                $stockPosterior = max(0, $stockAnterior - $cantidad);
            }

            $movimiento = MovimientoInventario::create([
                'id_inventario' => $inventario->id_inventario,
                'id_tipo_movimiento' => $data['id_tipo_movimiento'],
                'id_usuario' => auth()->id(),
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_posterior' => $stockPosterior,
                'referencia' => $data['referencia'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
                'created_at' => now(),
            ]);

            $inventario->update(['stock_actual' => $stockPosterior]);

            AuditService::log(auth()->id(), 'crear', 'movimientos_inventario', $movimiento->id_movimiento);

            return $this->jsonResponse(
                $movimiento->load('tipoMovimiento', 'usuario'),
                'Movimiento registrado exitosamente.',
                201
            );
        });
    }
}
