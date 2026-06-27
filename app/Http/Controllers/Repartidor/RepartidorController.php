<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\ApiController;
use App\Models\Repartidor\Repartidor;
use App\Models\Repartidor\HistorialEstadoRepartidor;
use App\Services\Logistica\AsignacionVehiculoService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class RepartidorController extends ApiController
{
    public function index(Request $request)
    {
        $query = Repartidor::with([
            'usuario',
            'estado',
            'licencia',
            'extensionCi',
            'asignacionVehiculoActiva.vehiculo.modelo.marca',
            'asignacionVehiculoActiva.vehiculo.capacidad',
        ]);

        if ($request->filled('id_estado_repartidor')) {
            $query->where('id_estado_repartidor', $request->id_estado_repartidor);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('usuario', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%");
            });
        }

        return $this->safePaginate(
            $query->orderBy('id_repartidor', 'desc'), $request
        );
    }

    public function store(Request $request, AsignacionVehiculoService $asignaciones)
    {
        $data = $request->validate([
            'id_usuario' => 'required|integer|exists:usuarios,id_usuario|unique:repartidores,id_usuario',
            'ci' => 'required|string|max:20|unique:repartidores,ci',
            'id_extension_ci' => 'required|integer|exists:extensiones_ci,id_extension_ci',
            'id_licencia' => 'required|integer|exists:licencias,id_licencia',
            'id_estado_repartidor' => 'required|integer|exists:estados_repartidor,id_estado_repartidor',
            'firebase_uid' => 'nullable|string|max:191|unique:repartidores,firebase_uid',
            'id_vehiculo' => 'nullable|integer|exists:vehiculos,id_vehiculo',
        ]);

        $idVehiculo = $data['id_vehiculo'] ?? null;
        unset($data['id_vehiculo']);

        $repartidor = Repartidor::create($data);

        HistorialEstadoRepartidor::create([
            'id_repartidor' => $repartidor->id_repartidor,
            'id_estado_repartidor' => $data['id_estado_repartidor'],
            'fecha_inicio' => now(),
        ]);

        if ($idVehiculo) {
            $asignaciones->asignar($repartidor->id_repartidor, (int) $idVehiculo, 'Asignacion inicial desde formulario de repartidor.');
        }

        return $this->jsonResponse(
            $repartidor->load('usuario', 'estado', 'licencia', 'extensionCi', 'asignacionVehiculoActiva.vehiculo.modelo.marca', 'asignacionVehiculoActiva.vehiculo.capacidad'),
            'Repartidor creado exitosamente.',
            201
        );
    }

    public function show($id)
    {
        $repartidor = Repartidor::with([
            'usuario',
            'estado',
            'licencia',
            'extensionCi',
            'asignacionVehiculoActiva.vehiculo.modelo.marca',
            'asignacionVehiculoActiva.vehiculo.capacidad',
            'historiales.estado',
            'controlRutas',
        ])->findOrFail($id);

        return $this->jsonResponse($repartidor);
    }

    public function update(Request $request, $id, AsignacionVehiculoService $asignaciones)
    {
        $repartidor = Repartidor::findOrFail($id);

        $data = $request->validate([
            'id_usuario' => 'sometimes|integer|exists:usuarios,id_usuario|unique:repartidores,id_usuario,' . $id . ',id_repartidor',
            'ci' => 'sometimes|string|max:20|unique:repartidores,ci,' . $id . ',id_repartidor',
            'id_extension_ci' => 'sometimes|integer|exists:extensiones_ci,id_extension_ci',
            'id_licencia' => 'sometimes|integer|exists:licencias,id_licencia',
            'id_estado_repartidor' => 'sometimes|integer|exists:estados_repartidor,id_estado_repartidor',
            'firebase_uid' => 'nullable|string|max:191|unique:repartidores,firebase_uid,' . $id . ',id_repartidor',
            'id_vehiculo' => 'nullable|integer|exists:vehiculos,id_vehiculo',
        ]);

        $idVehiculo = array_key_exists('id_vehiculo', $data) ? $data['id_vehiculo'] : null;
        unset($data['id_vehiculo']);

        if (isset($data['id_estado_repartidor']) && (int) $data['id_estado_repartidor'] !== (int) $repartidor->id_estado_repartidor) {
            $historialActual = HistorialEstadoRepartidor::where('id_repartidor', $id)
                ->whereNull('fecha_fin')
                ->latest('fecha_inicio')
                ->first();

            if ($historialActual) {
                $historialActual->update(['fecha_fin' => now()]);
            }

            HistorialEstadoRepartidor::create([
                'id_repartidor' => $id,
                'id_estado_repartidor' => $data['id_estado_repartidor'],
                'fecha_inicio' => now(),
            ]);
        }

        $repartidor->update($data);

        if ($request->has('id_vehiculo')) {
            if ($idVehiculo) {
                $asignaciones->asignar((int) $id, (int) $idVehiculo, 'Asignacion actualizada desde formulario de repartidor.');
            } else {
                $asignaciones->liberar((int) $id);
            }
        }

        return $this->jsonResponse(
            $repartidor->load('usuario', 'estado', 'licencia', 'extensionCi', 'asignacionVehiculoActiva.vehiculo.modelo.marca', 'asignacionVehiculoActiva.vehiculo.capacidad'),
            'Repartidor actualizado exitosamente.'
        );
    }

    public function destroy($id)
    {
        $repartidor = Repartidor::findOrFail($id);
        try {
            $repartidor->delete();
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return $this->errorResponse('No se puede eliminar este repartidor porque tiene registros relacionados.', 409);
            }

            throw $e;
        }

        return $this->jsonResponse(null, 'Repartidor eliminado exitosamente.');
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'id_estado_repartidor' => 'required|integer|exists:estados_repartidor,id_estado_repartidor',
        ]);

        $repartidor = Repartidor::findOrFail($id);

        $historialActual = HistorialEstadoRepartidor::where('id_repartidor', $id)
            ->whereNull('fecha_fin')
            ->latest('fecha_inicio')
            ->first();

        if ($historialActual) {
            $historialActual->update(['fecha_fin' => now()]);
        }

        HistorialEstadoRepartidor::create([
            'id_repartidor' => $id,
            'id_estado_repartidor' => $request->id_estado_repartidor,
            'fecha_inicio' => now(),
        ]);

        $repartidor->update(['id_estado_repartidor' => $request->id_estado_repartidor]);

        return $this->jsonResponse(
            $repartidor->load('estado', 'historiales.estado'),
            'Estado del repartidor actualizado exitosamente.'
        );
    }
}
