<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\ApiController;
use App\Models\Repartidor\Repartidor;
use App\Models\Repartidor\HistorialEstadoRepartidor;
use Illuminate\Http\Request;

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

        return $this->jsonResponse(
            $repartidor->load('usuario', 'estado', 'licencia', 'extensionCi'),
            'Repartidor actualizado exitosamente.'
        );
    }

    public function destroy($id)
    {
        $repartidor = Repartidor::findOrFail($id);
        $repartidor->delete();

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
