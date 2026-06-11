<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\ApiController;
use App\Models\Farmacia\ContactoFarmacia;
use App\Models\Farmacia\Farmacia;
use Illuminate\Http\Request;

class ContactoFarmaciaController extends ApiController
{
    public function index($farmaciaId)
    {
        $farmacia = Farmacia::findOrFail($farmaciaId);
        return $this->jsonResponse(
            $farmacia->contactos()->with('cargo')->get()
        );
    }

    public function store(Request $request, $farmaciaId)
    {
        $farmacia = Farmacia::findOrFail($farmaciaId);

        $data = $request->validate([
            'nombre_contacto' => 'required|string|max:150',
            'id_cargo' => 'required|integer|exists:cargos,id_cargo',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|email|max:180',
        ]);

        $data['id_farmacia'] = $farmacia->id_farmacia;
        $contacto = ContactoFarmacia::create($data);

        return $this->jsonResponse(
            $contacto->load('cargo'),
            'Contacto creado exitosamente.',
            201
        );
    }

    public function show($farmaciaId, $id)
    {
        $contacto = ContactoFarmacia::where('id_farmacia', $farmaciaId)
            ->with('cargo', 'farmacia')
            ->findOrFail($id);
        return $this->jsonResponse($contacto);
    }

    public function update(Request $request, $farmaciaId, $id)
    {
        $contacto = ContactoFarmacia::where('id_farmacia', $farmaciaId)->findOrFail($id);

        $data = $request->validate([
            'nombre_contacto' => 'sometimes|string|max:150',
            'id_cargo' => 'sometimes|integer|exists:cargos,id_cargo',
            'telefono' => 'sometimes|string|max:20',
            'email' => 'nullable|email|max:180',
        ]);

        $contacto->update($data);

        return $this->jsonResponse(
            $contacto->load('cargo'),
            'Contacto actualizado exitosamente.'
        );
    }

    public function destroy($farmaciaId, $id)
    {
        $contacto = ContactoFarmacia::where('id_farmacia', $farmaciaId)->findOrFail($id);
        $contacto->delete();

        return $this->jsonResponse(null, 'Contacto eliminado exitosamente.');
    }
}
