<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\ApiController;
use App\Models\Despacho\Despacho;
use App\Models\Evidencia\Incidencia;
use Illuminate\Http\Request;

class IncidenciaController extends ApiController
{
    public function index($despachoId)
    {
        $despacho = Despacho::findOrFail($despachoId);
        return $this->jsonResponse(
            $despacho->incidencias()->with('tipoIncidencia')->get()
        );
    }

    public function store(Request $request, $despachoId)
    {
        $despacho = Despacho::findOrFail($despachoId);

        $data = $request->validate([
            'id_tipo_incidencia' => 'required|integer|exists:tipos_incidencia,id_tipo_incidencia',
            'descripcion' => 'required|string',
        ]);

        $data['id_despacho'] = $despacho->id_despacho;
        $data['fecha_incidencia'] = now();

        $incidencia = Incidencia::create($data);

        return $this->jsonResponse(
            $incidencia->load('tipoIncidencia'),
            'Incidencia registrada exitosamente.',
            201
        );
    }

    public function show($despachoId, $id)
    {
        $incidencia = Incidencia::where('id_despacho', $despachoId)
            ->with('tipoIncidencia', 'despacho')
            ->findOrFail($id);
        return $this->jsonResponse($incidencia);
    }

    public function update(Request $request, $despachoId, $id)
    {
        $incidencia = Incidencia::where('id_despacho', $despachoId)->findOrFail($id);

        $data = $request->validate([
            'id_tipo_incidencia' => 'sometimes|integer|exists:tipos_incidencia,id_tipo_incidencia',
            'descripcion' => 'sometimes|string',
        ]);

        $incidencia->update($data);

        return $this->jsonResponse(
            $incidencia->load('tipoIncidencia'),
            'Incidencia actualizada exitosamente.'
        );
    }

    public function destroy($despachoId, $id)
    {
        $incidencia = Incidencia::where('id_despacho', $despachoId)->findOrFail($id);
        $incidencia->delete();

        return $this->jsonResponse(null, 'Incidencia eliminada exitosamente.');
    }
}
