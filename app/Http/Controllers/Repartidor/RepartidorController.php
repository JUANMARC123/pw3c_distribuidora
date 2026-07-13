<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\ApiController;
use App\Models\Repartidor;
use App\Models\Usuario;
use App\Services\Logistica\AsignacionVehiculoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RepartidorController extends ApiController
{
    protected $asignacionService;

    public function __construct(AsignacionVehiculoService $asignacionService)
    {
        $this->asignacionService = $asignacionService;
    }

    public function index(Request $request)
    {
        $query = Repartidor::with('usuario')
            ->with('asignacionActiva.vehiculo');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('ci', 'like', "%{$search}%")
                    ->orWhereHas('usuario', function ($uq) use ($search) {
                        $uq->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        return $this->safePaginate($query->orderBy('id_repartidor', 'desc'), $request);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_usuario' => 'required|integer|exists:usuarios,id_usuario|unique:repartidores,id_usuario',
            'ci' => 'required|string|max:20|unique:repartidores,ci',
            'categoria_licencia' => 'required|in:A,B,C',
            'firebase_uid' => 'nullable|string|max:255|unique:repartidores,firebase_uid',
        ]);

        $validated['estado'] = 'activo';

        DB::beginTransaction();
        try {
            $repartidor = Repartidor::create($validated);

            if ($request->filled('id_vehiculo')) {
                $this->asignacionService->asignar(
                    $repartidor->id_repartidor,
                    $request->id_vehiculo
                );
            }

            DB::commit();
            $repartidor->load('usuario', 'asignacionActiva.vehiculo');

            return $this->jsonResponse($repartidor, 'Repartidor creado exitosamente.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al crear repartidor: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $repartidor = Repartidor::with('usuario', 'asignacionesVehiculo.vehiculo', 'hojasRuta')
            ->with('asignacionActiva.vehiculo')
            ->findOrFail($id);

        return $this->jsonResponse($repartidor);
    }

    public function update(Request $request, $id)
    {
        $repartidor = Repartidor::findOrFail($id);

        $validated = $request->validate([
            'ci' => "sometimes|string|max:20|unique:repartidores,ci,{$id},id_repartidor",
            'categoria_licencia' => 'sometimes|in:A,B,C',
            'estado' => 'sometimes|in:activo,inactivo,en_ruta',
            'firebase_uid' => "nullable|string|max:255|unique:repartidores,firebase_uid,{$id},id_repartidor",
        ]);

        DB::beginTransaction();
        try {
            $repartidor->update($validated);

            if ($request->has('id_vehiculo')) {
                if ($request->id_vehiculo) {
                    $this->asignacionService->asignar(
                        $repartidor->id_repartidor,
                        $request->id_vehiculo
                    );
                } else {
                    $this->asignacionService->liberar($repartidor->id_repartidor);
                }
            }

            DB::commit();
            $repartidor->load('usuario', 'asignacionActiva.vehiculo');

            return $this->jsonResponse($repartidor, 'Repartidor actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error al actualizar repartidor: ' . $e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $repartidor = Repartidor::findOrFail($id);
            $repartidor->delete();
            return $this->jsonResponse(null, 'Repartidor eliminado exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse('No se puede eliminar el repartidor porque tiene registros asociados.', 409);
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $repartidor = Repartidor::findOrFail($id);

        $validated = $request->validate([
            'estado' => 'required|in:activo,inactivo,en_ruta',
        ]);

        $repartidor->update(['estado' => $validated['estado']]);

        return $this->jsonResponse($repartidor, 'Estado del repartidor actualizado.');
    }
}
