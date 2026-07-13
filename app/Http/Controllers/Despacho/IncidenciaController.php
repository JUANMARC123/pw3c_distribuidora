<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\ApiController;
use App\Models\Despacho;
use App\Models\Incidencia;
use Illuminate\Http\Request;

class IncidenciaController extends ApiController
{
    public function index($despachoId)
    {
        $despacho = Despacho::findOrFail($despachoId);
        return $this->jsonResponse($despacho->incidencias);
    }

    public function show($despachoId, $id)
    {
        $incidencia = Incidencia::where('id_despacho', $despachoId)->findOrFail($id);
        return $this->jsonResponse($incidencia);
    }

    public function store(Request $request, $despachoId)
    {
        $despacho = Despacho::findOrFail($despachoId);

        $validated = $request->validate([
            'tipo_incidencia' => 'required|in:cliente_ausente,producto_danado,retraso_trafico,devolucion_parcial,otro',
            'descripcion' => 'nullable|string|max:500',
            'archivo' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:2048',
        ]);

        $data = [
            'id_despacho' => $despacho->id_despacho,
            'tipo_incidencia' => $validated['tipo_incidencia'],
            'descripcion' => $validated['descripcion'] ?? null,
        ];

        if ($request->hasFile('archivo')) {
            $data['archivo'] = $request->file('archivo')->store('incidencias', 'public');
        }

        $incidencia = Incidencia::create($data);

        return $this->jsonResponse($incidencia, 'Incidencia registrada exitosamente.', 201);
    }

    public function update(Request $request, $despachoId, $id)
    {
        $incidencia = Incidencia::where('id_despacho', $despachoId)->findOrFail($id);

        $validated = $request->validate([
            'tipo_incidencia' => 'sometimes|in:cliente_ausente,producto_danado,retraso_trafico,devolucion_parcial,otro',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $incidencia->update($validated);

        return $this->jsonResponse($incidencia, 'Incidencia actualizada exitosamente.');
    }

    public function destroy($despachoId, $id)
    {
        $incidencia = Incidencia::where('id_despacho', $despachoId)->findOrFail($id);
        $incidencia->delete();

        return $this->jsonResponse(null, 'Incidencia eliminada exitosamente.');
    }
}
