<?php

namespace App\Http\Controllers\Producto;

use App\Http\Controllers\ApiController;
use App\Models\Producto\Producto;
use Illuminate\Http\Request;

class ProductoController extends ApiController
{
    public function index(Request $request)
    {
        $query = Producto::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nombre', 'like', "%{$search}%");
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->per_page === '-1') {
            return $this->jsonResponse($query->orderBy('nombre')->get());
        }

        return $this->paginatedResponse(
            $query->orderBy('nombre')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:200',
            'precio_unitario' => 'required|numeric|min:0',
            'unidad_medida' => 'nullable|string|max:20',
            'estado' => 'boolean',
        ]);

        $data['unidad_medida'] = $data['unidad_medida'] ?? 'UNIDAD';

        $producto = Producto::create($data);

        return $this->jsonResponse($producto, 'Producto creado exitosamente.', 201);
    }

    public function show($id)
    {
        $producto = Producto::findOrFail($id);
        return $this->jsonResponse($producto);
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:200',
            'precio_unitario' => 'sometimes|required|numeric|min:0',
            'unidad_medida' => 'nullable|string|max:20',
            'estado' => 'boolean',
        ]);

        $producto->update($data);

        return $this->jsonResponse($producto, 'Producto actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);

        if ($producto->detalles()->exists()) {
            return $this->errorResponse(
                'No se puede eliminar el producto porque tiene registros asociados en pedidos.',
                409
            );
        }

        $producto->delete();

        return $this->jsonResponse(null, 'Producto eliminado exitosamente.');
    }
}
