<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\ApiController;
use App\Models\Repartidor;
use App\Services\Logistica\AsignacionVehiculoService;
use Illuminate\Http\Request;

class RepartidorVehiculoController extends ApiController
{
    protected $asignacionService;

    public function __construct(AsignacionVehiculoService $asignacionService)
    {
        $this->asignacionService = $asignacionService;
    }

    public function show($repartidorId)
    {
        $repartidor = Repartidor::with('asignacionActiva.vehiculo')->findOrFail($repartidorId);
        return $this->jsonResponse($repartidor->asignacionActiva);
    }

    public function store(Request $request, $repartidorId)
    {
        $request->validate([
            'id_vehiculo' => 'required|integer|exists:vehiculos,id_vehiculo',
        ]);

        $asignacion = $this->asignacionService->asignar(
            (int) $repartidorId,
            (int) $request->id_vehiculo
        );

        return $this->jsonResponse($asignacion, 'Vehículo asignado exitosamente.', 201);
    }

    public function destroy($repartidorId)
    {
        $this->asignacionService->liberar((int) $repartidorId);
        return $this->jsonResponse(null, 'Vehículo desasignado exitosamente.');
    }
}
