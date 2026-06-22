<?php

namespace App\Http\Controllers\Venta;

use App\Http\Controllers\ApiController;
use App\Models\Venta\Venta;
use App\Models\Venta\DetalleVenta;
use App\Models\Venta\Pago;
use App\Models\Venta\MetodoPago;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends ApiController
{
    public function index(Request $request)
    {
        $query = Venta::with('pedido.farmacia', 'usuario', 'estado', 'detalles.lote.producto', 'pagos.metodoPago');

        if ($request->filled('id_estado_venta')) {
            $query->where('id_estado_venta', $request->id_estado_venta);
        }

        if ($request->filled('id_pedido')) {
            $query->where('id_pedido', $request->id_pedido);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_venta', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_venta', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('pedido', function ($q) use ($search) {
                $q->where('id_pedido', 'like', "%{$search}%");
            });
        }

        return $this->paginatedResponse(
            $query->orderBy('fecha_venta', 'desc')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_pedido' => 'required|integer|exists:pedidos,id_pedido',
            'id_estado_venta' => 'sometimes|integer|exists:estados_venta,id_estado_venta',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_lote' => 'required|integer|exists:lotes,id_lote',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data) {
            $detallesData = $data['detalles'];
            unset($data['detalles']);

            $data['id_usuario'] = auth()->id();
            $data['fecha_venta'] = now();
            $data['id_estado_venta'] = $data['id_estado_venta'] ?? 1;

            $detalles = [];
            $total = 0;

            foreach ($detallesData as $detalle) {
                $subtotal = round($detalle['cantidad'] * $detalle['precio_unitario'], 2);
                $total += $subtotal;
                $detalles[] = [
                    'id_lote' => $detalle['id_lote'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $subtotal,
                ];
            }

            $data['total'] = round($total, 2);

            $venta = Venta::create($data);

            foreach ($detalles as $detalle) {
                $detalle['id_venta'] = $venta->id_venta;
                DetalleVenta::create($detalle);
            }

            AuditService::log(auth()->id(), 'crear', 'ventas', $venta->id_venta);

            return $this->jsonResponse(
                $venta->load('pedido.farmacia', 'usuario', 'estado', 'detalles.lote.producto'),
                'Venta creada exitosamente.',
                201
            );
        });
    }

    public function show($id)
    {
        $venta = Venta::with([
            'pedido.farmacia',
            'pedido.contacto',
            'usuario',
            'estado',
            'detalles.lote.producto.categoria',
            'detalles.lote.producto.laboratorio',
            'pagos.metodoPago',
        ])->findOrFail($id);

        return $this->jsonResponse($venta);
    }

    public function update(Request $request, $id)
    {
        $venta = Venta::findOrFail($id);

        $data = $request->validate([
            'id_estado_venta' => 'sometimes|integer|exists:estados_venta,id_estado_venta',
            'detalles' => 'sometimes|array|min:1',
            'detalles.*.id_lote' => 'required_with:detalles|integer|exists:lotes,id_lote',
            'detalles.*.cantidad' => 'required_with:detalles|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required_with:detalles|numeric|min:0',
        ]);

        return DB::transaction(function () use ($venta, $data) {
            $venta->update(collect($data)->except('detalles')->toArray());

            if (isset($data['detalles'])) {
                $venta->detalles()->delete();

                $total = 0;
                foreach ($data['detalles'] as $detalle) {
                    $subtotal = round($detalle['cantidad'] * $detalle['precio_unitario'], 2);
                    $total += $subtotal;
                    DetalleVenta::create([
                        'id_venta' => $venta->id_venta,
                        'id_lote' => $detalle['id_lote'],
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $detalle['precio_unitario'],
                        'subtotal' => $subtotal,
                    ]);
                }
                $venta->update(['total' => round($total, 2)]);
            }

            AuditService::log(auth()->id(), 'editar', 'ventas', $venta->id_venta);

            return $this->jsonResponse(
                $venta->load('pedido.farmacia', 'usuario', 'estado', 'detalles.lote.producto'),
                'Venta actualizada exitosamente.'
            );
        });
    }

    public function destroy($id)
    {
        $venta = Venta::findOrFail($id);

        try {
            $venta->detalles()->delete();
            $venta->delete();
            AuditService::log(auth()->id(), 'eliminar', 'ventas', $id);
            return $this->jsonResponse(null, 'Venta eliminada exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return $this->errorResponse('No se puede eliminar la venta porque tiene pagos registrados.', 409);
            }
            throw $e;
        }
    }

    public function pagos($id)
    {
        $venta = Venta::findOrFail($id);
        return $this->jsonResponse(
            $venta->pagos()->with('metodoPago')->orderBy('fecha_pago', 'desc')->get()
        );
    }

    public function storePago(Request $request, $id)
    {
        $venta = Venta::findOrFail($id);

        $data = $request->validate([
            'id_metodo_pago' => 'required|integer|exists:metodos_pago,id_metodo_pago',
            'monto' => 'required|numeric|min:0.01',
            'referencia' => 'nullable|string|max:100',
        ]);

        $data['id_venta'] = $venta->id_venta;
        $data['fecha_pago'] = now();

        $pago = Pago::create($data);

        AuditService::log(auth()->id(), 'crear', 'ventas', $venta->id_venta);

        return $this->jsonResponse(
            $pago->load('metodoPago'),
            'Pago registrado exitosamente.',
            201
        );
    }

    public function destroyPago($id, $pagoId)
    {
        $pago = Pago::where('id_venta', $id)->findOrFail($pagoId);
        $pago->delete();

        return $this->jsonResponse(null, 'Pago eliminado exitosamente.');
    }
}
