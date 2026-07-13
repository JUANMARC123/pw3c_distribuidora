<?php

namespace App\Http\Controllers\Producto;

use App\Http\Controllers\ApiController;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends ApiController
{
    public function index(Request $request)
    {
        $query = Producto::query();

        if ($search = $request->search) {
            $query->where('nombre', 'like', "%{$search}%");
        }

        return $this->safePaginate($query->orderBy('nombre'), $request);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_unitario' => 'required|numeric|min:0',
            'unidad_medida' => 'required|in:CAJA,BLISTER,TUBO,FRASCO,AMPOLLA,KG',
        ]);

        $producto = Producto::create($validated);

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

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'precio_unitario' => 'sometimes|numeric|min:0',
            'unidad_medida' => 'sometimes|in:CAJA,BLISTER,TUBO,FRASCO,AMPOLLA,KG',
        ]);

        $producto->update($validated);

        return $this->jsonResponse($producto, 'Producto actualizado exitosamente.');
    }

    public function destroy($id)
    {
        try {
            $producto = Producto::findOrFail($id);
            $producto->delete();
            return $this->jsonResponse(null, 'Producto eliminado exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse('No se puede eliminar el producto porque tiene registros asociados.', 409);
        }
    }
}
