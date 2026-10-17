<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\ApiController;
use App\Models\ContactoFarmacia;
use App\Models\Farmacia;
use Illuminate\Http\Request;

class ContactoFarmaciaController extends ApiController
{
    public function index($farmaciaId)
    {
        $farmacia = Farmacia::findOrFail($farmaciaId);
        return $this->jsonResponse($farmacia->contactos);
    }

    public function show($farmaciaId, $id)
    {
        $contacto = ContactoFarmacia::where('id_farmacia', $farmaciaId)->findOrFail($id);
        return $this->jsonResponse($contacto);
    }

    public function store(Request $request, $farmaciaId)
    {
        $farmacia = Farmacia::findOrFail($farmaciaId);

        $validated = $request->validate([
            'nombre_contacto' => 'required|string|max:255',
            'cargo' => 'nullable|string|max:255',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|email|max:180',
        ]);

        $validated['id_farmacia'] = $farmacia->id_farmacia;
        $contacto = ContactoFarmacia::create($validated);

        return $this->jsonResponse($contacto, 'Contacto creado exitosamente.', 201);
    }

    public function update(Request $request, $farmaciaId, $id)
    {
        $contacto = ContactoFarmacia::where('id_farmacia', $farmaciaId)->findOrFail($id);

        $validated = $request->validate([
            'nombre_contacto' => 'sometimes|string|max:255',
            'cargo' => 'nullable|string|max:255',
            'telefono' => 'sometimes|string|max:20',
            'email' => 'nullable|email|max:180',
        ]);

        $contacto->update($validated);

        return $this->jsonResponse($contacto, 'Contacto actualizado exitosamente.');
    }

    public function destroy($farmaciaId, $id)
    {
        $contacto = ContactoFarmacia::where('id_farmacia', $farmaciaId)->findOrFail($id);
        $contacto->delete();

        return $this->jsonResponse(null, 'Contacto eliminado exitosamente.');
    }
}
