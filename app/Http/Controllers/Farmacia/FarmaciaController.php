<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\ApiController;
use App\Models\Farmacia\Farmacia;
use Illuminate\Http\Request;

class FarmaciaController extends ApiController
{
    public function index(Request $request)
    {
        $query = Farmacia::withCount('contactos');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('direccion', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        return $this->paginatedResponse(
            $query->orderBy('nombre')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'direccion' => 'required|string',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|email|max:180|unique:farmacias,email',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
        ]);

        $farmacia = Farmacia::create($data);

        return $this->jsonResponse($farmacia, 'Farmacia creada exitosamente.', 201);
    }

    public function show($id)
    {
        $farmacia = Farmacia::with('contactos.cargo')->findOrFail($id);
        return $this->jsonResponse($farmacia);
    }

    public function update(Request $request, $id)
    {
        $farmacia = Farmacia::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|string|max:150',
            'direccion' => 'sometimes|string',
            'telefono' => 'sometimes|string|max:20',
            'email' => 'nullable|email|max:180|unique:farmacias,email,' . $id . ',id_farmacia',
            'latitud' => 'sometimes|numeric|between:-90,90',
            'longitud' => 'sometimes|numeric|between:-180,180',
        ]);

        $farmacia->update($data);

        return $this->jsonResponse($farmacia, 'Farmacia actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $farmacia = Farmacia::withCount(['contactos', 'pedidos'])->findOrFail($id);

        if ($farmacia->pedidos_count > 0) {
            return $this->errorResponse(
                "No se puede eliminar la farmacia \"{$farmacia->nombre}\" porque tiene {$farmacia->pedidos_count} pedido(s) registrado(s). Elimine primero los pedidos asociados.",
                409
            );
        }

        // Eliminar contactos primero (por si acaso)
        $farmacia->contactos()->delete();
        $farmacia->delete();

        return $this->jsonResponse(null, 'Farmacia eliminada exitosamente.');
    }
}
