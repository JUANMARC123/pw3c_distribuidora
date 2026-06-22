<?php

namespace App\Http\Controllers\Vehiculo;

use App\Http\Controllers\ApiController;
use App\Models\Vehiculo\Vehiculo;
use App\Models\Vehiculo\HistorialEstadoVehiculo;
use Illuminate\Http\Request;
use App\Services\AuditService;

class VehiculoController extends ApiController
{
    public function index(Request $request)
    {
        $query = Vehiculo::with('modelo.marca', 'capacidad', 'estado');

        if ($request->filled('id_estado_vehiculo')) {
            $query->where('id_estado_vehiculo', $request->id_estado_vehiculo);
        }

        if ($request->filled('id_modelo')) {
            $query->where('id_modelo', $request->id_modelo);
        }

        if ($request->filled('search')) {
            $query->where('placa', 'like', "%{$request->search}%");
        }

        return $this->paginatedResponse(
            $query->orderBy('id_vehiculo', 'desc')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'placa' => 'required|string|max:20|unique:vehiculos,placa',
            'id_modelo' => 'required|integer|exists:modelos,id_modelo',
            'id_capacidad' => 'required|integer|exists:capacidades,id_capacidad',
            'id_estado_vehiculo' => 'required|integer|exists:estados_vehiculo,id_estado_vehiculo',
        ]);

        $vehiculo = Vehiculo::create($data);

        AuditService::log(auth()->id(), 'crear', 'vehiculos', $vehiculo->id_vehiculo);

        HistorialEstadoVehiculo::create([
            'id_vehiculo' => $vehiculo->id_vehiculo,
            'id_estado_vehiculo' => $data['id_estado_vehiculo'],
            'fecha_inicio' => now(),
        ]);

        return $this->jsonResponse(
            $vehiculo->load('modelo.marca', 'capacidad', 'estado'),
            'Vehículo creado exitosamente.',
            201
        );
    }

    public function show($id)
    {
        $vehiculo = Vehiculo::with([
            'modelo.marca',
            'capacidad',
            'estado',
            'historiales.estado',
            'controlRutas',
        ])->findOrFail($id);

        return $this->jsonResponse($vehiculo);
    }

    public function update(Request $request, $id)
    {
        $vehiculo = Vehiculo::findOrFail($id);

        $data = $request->validate([
            'placa' => 'sometimes|string|max:20|unique:vehiculos,placa,' . $id . ',id_vehiculo',
            'id_modelo' => 'sometimes|integer|exists:modelos,id_modelo',
            'id_capacidad' => 'sometimes|integer|exists:capacidades,id_capacidad',
        ]);

        $vehiculo->update($data);

        AuditService::log(auth()->id(), 'editar', 'vehiculos', $vehiculo->id_vehiculo);

        return $this->jsonResponse(
            $vehiculo->load('modelo.marca', 'capacidad', 'estado'),
            'Vehículo actualizado exitosamente.'
        );
    }

    public function destroy($id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        $vehiculo->delete();

        AuditService::log(auth()->id(), 'eliminar', 'vehiculos', $id);

        return $this->jsonResponse(null, 'Vehículo eliminado exitosamente.');
    }

    private const TRANSICIONES_VEHICULO = [
        1 => [2],
        2 => [1, 3],
        3 => [1],
    ];

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'id_estado_vehiculo' => 'required|integer|exists:estados_vehiculo,id_estado_vehiculo',
        ]);

        $vehiculo = Vehiculo::findOrFail($id);
        $nuevoEstado = (int) $request->id_estado_vehiculo;
        $estadoActual = (int) $vehiculo->id_estado_vehiculo;

        $transicionesValidas = self::TRANSICIONES_VEHICULO[$estadoActual] ?? [];
        if (!empty($transicionesValidas) && !in_array($nuevoEstado, $transicionesValidas)) {
            return $this->errorResponse(
                "Transición de estado no válida. No se puede cambiar de " .
                "{$vehiculo->estado->nombre_estado} al estado solicitado.",
                422
            );
        }

        if ($nuevoEstado === $estadoActual) {
            return $this->errorResponse('El vehículo ya se encuentra en este estado.', 422);
        }

        $historialActual = HistorialEstadoVehiculo::where('id_vehiculo', $id)
            ->whereNull('fecha_fin')
            ->latest('fecha_inicio')
            ->first();

        if ($historialActual) {
            $historialActual->update(['fecha_fin' => now()]);
        }

        HistorialEstadoVehiculo::create([
            'id_vehiculo' => $id,
            'id_estado_vehiculo' => $nuevoEstado,
            'fecha_inicio' => now(),
        ]);

        $vehiculo->update(['id_estado_vehiculo' => $nuevoEstado]);

        AuditService::log(auth()->id(), 'cambiar-estado', 'vehiculos', $vehiculo->id_vehiculo);

        return $this->jsonResponse(
            $vehiculo->load('estado', 'historiales.estado'),
            'Estado del vehículo actualizado exitosamente.'
        );
    }
}
