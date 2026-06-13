<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\ApiController;
use App\Models\Despacho\Despacho;
use App\Models\Evidencia\EvidenciaEntrega;
use Illuminate\Http\Request;

class EvidenciaController extends ApiController
{
    public function index($despachoId)
    {
        $despacho = Despacho::findOrFail($despachoId);
        return $this->jsonResponse(
            $despacho->evidencias()->with('tipoEvidencia')->get()
        );
    }

    public function store(Request $request, $despachoId)
    {
        $despacho = Despacho::findOrFail($despachoId);

        $request->validate([
            'id_tipo_evidencia' => 'required|integer|exists:tipos_evidencia,id_tipo_evidencia',
            'archivo' => 'required|file|mimes:jpg,jpeg,png,pdf,gif|max:5120',
        ]);

        $path = $request->file('archivo')->store('evidencias', 'public');

        $evidencia = EvidenciaEntrega::create([
            'id_despacho'       => $despacho->id_despacho,
            'id_tipo_evidencia' => $request->id_tipo_evidencia,
            'archivo'           => $path,
            'fecha_registro'    => now(),
        ]);

        return $this->jsonResponse(
            $evidencia->load('tipoEvidencia'),
            'Evidencia registrada exitosamente.',
            201
        );
    }

    public function show($despachoId, $id)
    {
        $evidencia = EvidenciaEntrega::where('id_despacho', $despachoId)
            ->with('tipoEvidencia', 'despacho')
            ->findOrFail($id);
        return $this->jsonResponse($evidencia);
    }

    public function update(Request $request, $despachoId, $id)
    {
        $evidencia = EvidenciaEntrega::where('id_despacho', $despachoId)->findOrFail($id);

        $data = $request->validate([
            'id_tipo_evidencia' => 'sometimes|integer|exists:tipos_evidencia,id_tipo_evidencia',
            'archivo' => 'sometimes|string|max:255',
        ]);

        $evidencia->update($data);

        return $this->jsonResponse(
            $evidencia->load('tipoEvidencia'),
            'Evidencia actualizada exitosamente.'
        );
    }

    public function destroy($despachoId, $id)
    {
        $evidencia = EvidenciaEntrega::where('id_despacho', $despachoId)->findOrFail($id);
        $evidencia->delete();

        return $this->jsonResponse(null, 'Evidencia eliminada exitosamente.');
    }
}
