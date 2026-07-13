<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\ApiController;
use App\Models\Farmacia;
use Illuminate\Http\Request;

class FarmaciaController extends ApiController
{
    public function index(Request $request)
    {
        $query = Farmacia::withCount('contactos');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        return $this->safePaginate($query->orderBy('nombre'), $request);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string',
            'telefono' => 'required|string|max:20',
            'email' => 'required|email|max:180|unique:farmacias,email',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'geocerca' => 'nullable|numeric|min:0',
        ]);

        $farmacia = Farmacia::create($validated);

        return $this->jsonResponse($farmacia, 'Farmacia creada exitosamente.', 201);
    }

    public function show($id)
    {
        $farmacia = Farmacia::with('contactos')->findOrFail($id);
        return $this->jsonResponse($farmacia);
    }

    public function update(Request $request, $id)
    {
        $farmacia = Farmacia::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'direccion' => 'sometimes|string',
            'telefono' => 'sometimes|string|max:20',
            'email' => "sometimes|email|max:180|unique:farmacias,email,{$id},id_farmacia",
            'latitud' => 'sometimes|numeric',
            'longitud' => 'sometimes|numeric',
            'geocerca' => 'nullable|numeric|min:0',
        ]);

        $farmacia->update($validated);

        return $this->jsonResponse($farmacia, 'Farmacia actualizada exitosamente.');
    }

    public function destroy($id)
    {
        try {
            $farmacia = Farmacia::findOrFail($id);
            $farmacia->delete();
            return $this->jsonResponse(null, 'Farmacia eliminada exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse('No se puede eliminar la farmacia porque tiene registros asociados.', 409);
        }
    }
}
