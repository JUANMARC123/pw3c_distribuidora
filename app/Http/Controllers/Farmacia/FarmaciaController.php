<?php

namespace App\Http\Controllers\Farmacia;

use App\Http\Controllers\ApiController;
use App\Models\Farmacia\Farmacia;
use Illuminate\Http\Request;
use App\Services\AuditService;

class FarmaciaController extends ApiController
{
    public function index(Request $request)
    {
        $query = Farmacia::with('estado')->withCount('contactos');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('direccion', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        if ($request->filled('id_estado_farmacia')) {
            $query->where('id_estado_farmacia', $request->id_estado_farmacia);
        }

        if ($request->filled('zona')) {
            $query->where('zona', 'like', "%{$request->zona}%");
        }

        return $this->paginatedResponse(
            $query->orderBy('nombre')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'direccion' => 'required|string',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|email|max:180|unique:farmacias,email',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'id_estado_farmacia' => 'sometimes|integer|exists:estados_farmacia,id_estado_farmacia',
            'zona' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string',
            'es_24_horas' => 'nullable|boolean',
            'horario_apertura' => 'nullable|date_format:H:i:s',
            'horario_cierre' => 'nullable|date_format:H:i:s|after:horario_apertura',
            'fecha_verificacion' => 'nullable|date',
        ]);

        $farmacia = Farmacia::create($data);

        AuditService::log(auth()->id(), 'crear', 'farmacias', $farmacia->id_farmacia);

        return $this->jsonResponse($farmacia->load('estado'), 'Farmacia creada exitosamente.', 201);
    }

    public function show($id)
    {
        $farmacia = Farmacia::with('estado', 'contactos.cargo')->findOrFail($id);
        return $this->jsonResponse($farmacia);
    }

    public function update(Request $request, $id)
    {
        $farmacia = Farmacia::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|string|max:150',
            'direccion' => 'sometimes|string',
            'telefono' => 'sometimes|string|max:20',
            'email' => 'nullable|email|max:180|unique:farmacias,email,' . $id . ',id_farmacia',
            'latitud' => 'sometimes|numeric|between:-90,90',
            'longitud' => 'sometimes|numeric|between:-180,180',
            'id_estado_farmacia' => 'sometimes|integer|exists:estados_farmacia,id_estado_farmacia',
            'zona' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string',
            'es_24_horas' => 'nullable|boolean',
            'horario_apertura' => 'nullable|date_format:H:i:s',
            'horario_cierre' => 'nullable|date_format:H:i:s|after:horario_apertura',
            'fecha_verificacion' => 'nullable|date',
        ]);

        $farmacia->update($data);

        AuditService::log(auth()->id(), 'editar', 'farmacias', $farmacia->id_farmacia);

        return $this->jsonResponse($farmacia->load('estado'), 'Farmacia actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $farmacia = Farmacia::findOrFail($id);
        $farmacia->delete();

        AuditService::log(auth()->id(), 'eliminar', 'farmacias', $id);

        return $this->jsonResponse(null, 'Farmacia eliminada exitosamente.');
    }
}
