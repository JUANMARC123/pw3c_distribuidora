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
<<<<<<< HEAD
        $query = Farmacia::with('estado')->withCount('contactos');
=======
        $query = Farmacia::withCount('contactos');
>>>>>>> a8d1a4151a519e9d6236de86f1e75b9755f1c273

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('direccion', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

<<<<<<< HEAD
        if ($request->filled('id_estado_farmacia')) {
            $query->where('id_estado_farmacia', $request->id_estado_farmacia);
        }

        if ($request->filled('zona')) {
            $query->where('zona', 'like', "%{$request->zona}%");
        }

=======
>>>>>>> a8d1a4151a519e9d6236de86f1e75b9755f1c273
        return $this->paginatedResponse(
            $query->orderBy('nombre')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'    => 'required|string|max:150',
            'direccion' => 'required|string',
<<<<<<< HEAD
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
=======
            'telefono'  => 'required|string|max:20',
            'email'     => 'nullable|email|max:180|unique:farmacias,email',
            'latitud'   => 'required|numeric|between:-90,90',
            'longitud'  => 'required|numeric|between:-180,180',
>>>>>>> a8d1a4151a519e9d6236de86f1e75b9755f1c273
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
            'nombre'    => 'sometimes|string|max:150',
            'direccion' => 'sometimes|string',
<<<<<<< HEAD
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
=======
            'telefono'  => 'sometimes|string|max:20',
            'email'     => 'nullable|email|max:180|unique:farmacias,email,' . $id . ',id_farmacia',
            'latitud'   => 'sometimes|numeric|between:-90,90',
            'longitud'  => 'sometimes|numeric|between:-180,180',
>>>>>>> a8d1a4151a519e9d6236de86f1e75b9755f1c273
        ]);

        $farmacia->update($data);

        AuditService::log(auth()->id(), 'editar', 'farmacias', $farmacia->id_farmacia);

        return $this->jsonResponse($farmacia->load('estado'), 'Farmacia actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $farmacia = Farmacia::withCount('pedidos')->findOrFail($id);

        if ($farmacia->pedidos_count > 0) {
            return $this->errorResponse(
                'No se puede eliminar la farmacia "' . $farmacia->nombre . '" porque tiene ' . $farmacia->pedidos_count . ' pedido(s) registrado(s). Elimine primero los pedidos asociados.',
                409
            );
        }

        $farmacia->contactos()->delete();
        $farmacia->delete();

        AuditService::log(auth()->id(), 'eliminar', 'farmacias', $id);

        return $this->jsonResponse(null, 'Farmacia eliminada exitosamente.');
    }
}