<?php

namespace App\Http\Controllers\Vehiculo;

use App\Http\Controllers\ApiController;
use App\Models\Vehiculo;
use Illuminate\Http\Request;

class VehiculoController extends ApiController
{
    public function index(Request $request)
    {
        $query = Vehiculo::query();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('placa', 'like', "%{$search}%")
                    ->orWhere('marca', 'like', "%{$search}%")
                    ->orWhere('modelo', 'like', "%{$search}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        return $this->safePaginate($query->orderBy('placa'), $request);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'placa' => 'required|string|max:20|unique:vehiculos,placa',
            'marca' => 'required|string|max:100',
            'modelo' => 'required|string|max:100',
            'capacidad_kg' => 'nullable|integer|min:0',
            'estado' => 'sometimes|in:disponible,taller,en_ruta',
        ]);

        $vehiculo = Vehiculo::create($validated);

        return $this->jsonResponse($vehiculo, 'Vehículo creado exitosamente.', 201);
    }

    public function show($id)
    {
        $vehiculo = Vehiculo::with('asignaciones.repartidor.usuario', 'hojasRuta')
            ->findOrFail($id);

        return $this->jsonResponse($vehiculo);
    }

    public function update(Request $request, $id)
    {
        $vehiculo = Vehiculo::findOrFail($id);

        $validated = $request->validate([
            'placa' => "sometimes|string|max:20|unique:vehiculos,placa,{$id},id_vehiculo",
            'marca' => 'sometimes|string|max:100',
            'modelo' => 'sometimes|string|max:100',
            'capacidad_kg' => 'nullable|integer|min:0',
            'estado' => 'sometimes|in:disponible,taller,en_ruta',
        ]);

        $vehiculo->update($validated);

        return $this->jsonResponse($vehiculo, 'Vehículo actualizado exitosamente.');
    }

    public function destroy($id)
    {
        try {
            $vehiculo = Vehiculo::findOrFail($id);
            $vehiculo->delete();
            return $this->jsonResponse(null, 'Vehículo eliminado exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse('No se puede eliminar el vehículo porque tiene registros asociados.', 409);
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $vehiculo = Vehiculo::findOrFail($id);

        $validated = $request->validate([
            'estado' => 'required|in:disponible,taller,en_ruta',
        ]);

        $vehiculo->update(['estado' => $validated['estado']]);

        return $this->jsonResponse($vehiculo, 'Estado del vehículo actualizado.');
    }
}
