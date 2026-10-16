<?php

namespace App\Http\Controllers\Producto;

use App\Http\Controllers\ApiController;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductoController extends ApiController
{
    /**
     * Unidades admitidas. Debe seguir la lista de UNIDADES del front-end
     * (public/js/modules/productos.js) y el ENUM de la migración
     * 2026_10_13_120000_widen_unidad_medida_productos.
     */
    private const UNIDADES = [
        'UNIDAD', 'CAJA', 'BLISTER', 'TUBO', 'FRASCO', 'AMPOLLA', 'KG',
        'LITRO', 'ML', 'MG', 'G', 'ROLLO', 'PAR', 'PAQUETE',
    ];

    public function index(Request $request): JsonResponse
    {
        $query = Producto::query();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('unidad_medida', 'like', "%{$search}%");
            });
        }

        // El front manda estado como "1"/"0"/true/false; solo se filtra si viene explícito.
        if ($request->filled('estado')) {
            $query->where('estado', filter_var($request->estado, FILTER_VALIDATE_BOOLEAN));
        }

        return $this->safePaginate($query->orderBy('nombre'), $request);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre'          => 'required|string|max:200|unique:productos,nombre',
            'precio_unitario' => 'required|numeric|min:0.01',
            'unidad_medida'   => 'sometimes|nullable|string|in:' . implode(',', self::UNIDADES),
            'estado'          => 'sometimes|nullable|boolean',
        ]);

        $validated['unidad_medida'] = $validated['unidad_medida'] ?? 'UNIDAD';
        $validated['estado']        = $request->has('estado') ? (bool) $validated['estado'] : true;

        $producto = Producto::create($validated);

        return $this->jsonResponse($producto, 'Producto creado exitosamente.', 201);
    }

    public function show($id): JsonResponse
    {
        $producto = Producto::findOrFail($id);

        return $this->jsonResponse($producto);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $producto = Producto::findOrFail($id);

        $validated = $request->validate([
            'nombre'          => "sometimes|required|string|max:200|unique:productos,nombre,{$id},id_producto",
            'precio_unitario' => 'sometimes|required|numeric|min:0.01',
            'unidad_medida'   => 'sometimes|nullable|string|in:' . implode(',', self::UNIDADES),
            'estado'          => 'sometimes|nullable|boolean',
        ]);

        if (array_key_exists('estado', $validated)) {
            $validated['estado'] = (bool) $validated['estado'];
        }

        $producto->update($validated);

        return $this->jsonResponse($producto->refresh(), 'Producto actualizado exitosamente.');
    }

    /**
     * Borrado lógico. La FK detalle_pedido.id_producto tiene ON DELETE CASCADE,
     * así que un destroy() físico arrastraría los detalles de pedidos ya
     * registrados. Se desactiva el producto y se deja el historial intacto.
     */
    public function destroy($id): JsonResponse
    {
        $producto = Producto::findOrFail($id);

        if ($producto->detalles()->exists()) {
            $producto->update(['estado' => false]);

            return $this->jsonResponse(
                ['id_producto' => $producto->id_producto, 'estado' => false],
                'El producto tiene detalles de pedido asociados y fue desactivado en lugar de eliminado.'
            );
        }

        $producto->delete();

        return $this->jsonResponse(null, 'Producto eliminado exitosamente.');
    }
}
