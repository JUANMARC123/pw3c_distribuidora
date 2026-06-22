<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\ApiController;
use App\Models\Repartidor\Repartidor;
use App\Models\Repartidor\HistorialEstadoRepartidor;
use Illuminate\Http\Request;
use App\Services\AuditService;

class RepartidorController extends ApiController
{
    public function index(Request $request)
    {
        $query = Repartidor::with('usuario', 'estado', 'licencia');

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

        return $this->paginatedResponse(
            $query->orderBy('id_repartidor', 'desc')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_usuario' => 'required|integer|exists:usuarios,id_usuario|unique:repartidores,id_usuario',
            'ci' => 'required|string|max:20|unique:repartidores,ci',
            'id_extension_ci' => 'required|integer|exists:extensiones_ci,id_extension_ci',
            'id_licencia' => 'required|integer|exists:licencias,id_licencia',
            'id_estado_repartidor' => 'required|integer|exists:estados_repartidor,id_estado_repartidor',
        ]);

        $repartidor = Repartidor::create($data);

        AuditService::log(auth()->id(), 'crear', 'repartidores', $repartidor->id_repartidor);

        HistorialEstadoRepartidor::create([
            'id_repartidor' => $repartidor->id_repartidor,
            'id_estado_repartidor' => $data['id_estado_repartidor'],
            'fecha_inicio' => now(),
        ]);

        return $this->jsonResponse(
            $repartidor->load('usuario', 'estado', 'licencia', 'extensionCi'),
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
            'historiales.estado',
            'controlRutas',
        ])->findOrFail($id);

        return $this->jsonResponse($repartidor);
    }

    public function update(Request $request, $id)
    {
        $repartidor = Repartidor::findOrFail($id);

        $data = $request->validate([
            'ci' => 'sometimes|string|max:20|unique:repartidores,ci,' . $id . ',id_repartidor',
            'id_extension_ci' => 'sometimes|integer|exists:extensiones_ci,id_extension_ci',
            'id_licencia' => 'sometimes|integer|exists:licencias,id_licencia',
        ]);

        $repartidor->update($data);

        AuditService::log(auth()->id(), 'editar', 'repartidores', $repartidor->id_repartidor);

        return $this->jsonResponse(
            $repartidor->load('usuario', 'estado', 'licencia', 'extensionCi'),
            'Repartidor actualizado exitosamente.'
        );
    }

    public function destroy($id)
    {
        $repartidor = Repartidor::findOrFail($id);
        $repartidor->delete();

        AuditService::log(auth()->id(), 'eliminar', 'repartidores', $id);

        return $this->jsonResponse(null, 'Repartidor eliminado exitosamente.');
    }

    private const TRANSICIONES_REPARTIDOR = [
        1 => [2],
        2 => [1, 3],
        3 => [1],
    ];

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'id_estado_repartidor' => 'required|integer|exists:estados_repartidor,id_estado_repartidor',
        ]);

        $repartidor = Repartidor::findOrFail($id);
        $nuevoEstado = (int) $request->id_estado_repartidor;
        $estadoActual = (int) $repartidor->id_estado_repartidor;

        $transicionesValidas = self::TRANSICIONES_REPARTIDOR[$estadoActual] ?? [];
        if (!empty($transicionesValidas) && !in_array($nuevoEstado, $transicionesValidas)) {
            return $this->errorResponse(
                "Transición de estado no válida. No se puede cambiar de " .
                "{$repartidor->estado->nombre_estado} al estado solicitado.",
                422
            );
        }

        if ($nuevoEstado === $estadoActual) {
            return $this->errorResponse('El repartidor ya se encuentra en este estado.', 422);
        }

        $historialActual = HistorialEstadoRepartidor::where('id_repartidor', $id)
            ->whereNull('fecha_fin')
            ->latest('fecha_inicio')
            ->first();

        if ($historialActual) {
            $historialActual->update(['fecha_fin' => now()]);
        }

        HistorialEstadoRepartidor::create([
            'id_repartidor' => $id,
            'id_estado_repartidor' => $nuevoEstado,
            'fecha_inicio' => now(),
        ]);

        $repartidor->update(['id_estado_repartidor' => $nuevoEstado]);

        AuditService::log(auth()->id(), 'cambiar-estado', 'repartidores', $repartidor->id_repartidor);

        return $this->jsonResponse(
            $repartidor->load('estado', 'historiales.estado'),
            'Estado del repartidor actualizado exitosamente.'
        );
    }
}
