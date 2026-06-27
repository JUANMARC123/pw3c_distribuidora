<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\ApiController;
use App\Models\Repartidor\Repartidor;
use App\Services\Logistica\AsignacionVehiculoService;
use Illuminate\Http\Request;

class RepartidorVehiculoController extends ApiController
{
    public function show($id)
    {
        $repartidor = Repartidor::with('asignacionVehiculoActiva.vehiculo.modelo.marca', 'asignacionVehiculoActiva.vehiculo.capacidad')
            ->findOrFail($id);

        return $this->jsonResponse($repartidor->asignacionVehiculoActiva);
    }

    public function store(Request $request, $id, AsignacionVehiculoService $service)
    {
        Repartidor::findOrFail($id);

        $data = $request->validate([
            'id_vehiculo' => 'required|integer|exists:vehiculos,id_vehiculo',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $asignacion = $service->asignar((int) $id, (int) $data['id_vehiculo'], $data['observaciones'] ?? null);

        return $this->jsonResponse(
            $asignacion->load('vehiculo.modelo.marca', 'vehiculo.capacidad'),
            'Vehiculo asignado exitosamente.',
            201
        );
    }

    public function destroy($id, AsignacionVehiculoService $service)
    {
        Repartidor::findOrFail($id);
        $service->liberar((int) $id);

        return $this->jsonResponse(null, 'Vehiculo liberado exitosamente.');
    }
}
