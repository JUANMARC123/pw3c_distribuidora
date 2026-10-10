<?php

namespace App\Http\Controllers\Despacho;

use App\Http\Controllers\ApiController;
use App\Models\Despacho;
use App\Models\EvidenciaEntrega;
use Illuminate\Http\Request;

class EvidenciaController extends ApiController
{
    public function index($despachoId)
    {
        $despacho = Despacho::findOrFail($despachoId);
        return $this->jsonResponse($despacho->evidencias);
    }

    public function show($despachoId, $id)
    {
        $evidencia = EvidenciaEntrega::where('id_despacho', $despachoId)->findOrFail($id);
        return $this->jsonResponse($evidencia);
    }

    public function store(Request $request, $despachoId)
    {
        $despacho = Despacho::findOrFail($despachoId);

        $validated = $request->validate([
            'tipo_evidencia' => 'required|in:firma,foto,sello',
            'archivo' => 'required|file|mimes:jpg,jpeg,png,gif,pdf,webp,heic,heif|max:10240',
        ]);

        $ruta = $request->file('archivo')->store('evidencias', 'public');

        $evidencia = EvidenciaEntrega::create([
            'id_despacho' => $despacho->id_despacho,
            'tipo_evidencia' => $validated['tipo_evidencia'],
            'archivo' => $ruta,
        ]);

        return $this->jsonResponse($evidencia, 'Evidencia registrada exitosamente.', 201);
    }

    public function update(Request $request, $despachoId, $id)
    {
        $evidencia = EvidenciaEntrega::where('id_despacho', $despachoId)->findOrFail($id);

        $validated = $request->validate([
            'tipo_evidencia' => 'sometimes|in:firma,foto,sello',
        ]);

        if ($request->hasFile('archivo')) {
            $request->validate(['archivo' => 'file|mimes:jpg,jpeg,png,gif,pdf,webp,heic,heif|max:10240']);
            $validated['archivo'] = $request->file('archivo')->store('evidencias', 'public');
        }

        $evidencia->update($validated);

        return $this->jsonResponse($evidencia, 'Evidencia actualizada exitosamente.');
    }

    public function destroy($despachoId, $id)
    {
        $evidencia = EvidenciaEntrega::where('id_despacho', $despachoId)->findOrFail($id);
        $evidencia->delete();

        return $this->jsonResponse(null, 'Evidencia eliminada exitosamente.');
    }
}
