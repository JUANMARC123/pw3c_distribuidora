<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\ApiController;
use App\Models\Inventario\Almacen;
use App\Models\Inventario\UbicacionAlmacen;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class AlmacenController extends ApiController
{
    public function index(Request $request)
    {
        $query = Almacen::with('farmacia', 'ubicaciones');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhereHas('farmacia', function ($q2) use ($search) {
                      $q2->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('id_farmacia')) {
            $query->where('id_farmacia', $request->id_farmacia);
        }

        return $this->paginatedResponse(
            $query->orderBy('nombre')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_farmacia' => 'required|integer|exists:farmacias,id_farmacia',
            'nombre' => 'required|string|max:100',
        ]);

        $exists = Almacen::where('id_farmacia', $data['id_farmacia'])
            ->where('nombre', $data['nombre'])->exists();

        if ($exists) {
            return $this->errorResponse('Ya existe un almacén con ese nombre en la misma farmacia.', 422);
        }

        $almacen = Almacen::create($data);

        AuditService::log(auth()->id(), 'crear', 'almacenes', $almacen->id_almacen);

        return $this->jsonResponse($almacen->load('farmacia'), 'Almacén creado exitosamente.', 201);
    }

    public function show($id)
    {
        $almacen = Almacen::with('farmacia', 'ubicaciones')->findOrFail($id);
        return $this->jsonResponse($almacen);
    }

    public function update(Request $request, $id)
    {
        $almacen = Almacen::findOrFail($id);

        $data = $request->validate([
            'id_farmacia' => 'sometimes|integer|exists:farmacias,id_farmacia',
            'nombre' => 'sometimes|string|max:100',
        ]);

        if (isset($data['nombre']) || isset($data['id_farmacia'])) {
            $farmId = $data['id_farmacia'] ?? $almacen->id_farmacia;
            $exists = Almacen::where('id_farmacia', $farmId)
                ->where('nombre', $data['nombre'] ?? $almacen->nombre)
                ->where('id_almacen', '!=', $id)
                ->exists();

            if ($exists) {
                return $this->errorResponse('Ya existe un almacén con ese nombre en la misma farmacia.', 422);
            }
        }

        $almacen->update($data);

        AuditService::log(auth()->id(), 'editar', 'almacenes', $almacen->id_almacen);

        return $this->jsonResponse($almacen->load('farmacia'), 'Almacén actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $almacen = Almacen::findOrFail($id);

        try {
            $almacen->delete();

            AuditService::log(auth()->id(), 'eliminar', 'almacenes', $id);

            return $this->jsonResponse(null, 'Almacén eliminado exitosamente.');
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                return $this->errorResponse('No se puede eliminar el almacén porque tiene ubicaciones registradas.', 409);
            }
            throw $e;
        }
    }

    public function ubicaciones($id)
    {
        $almacen = Almacen::findOrFail($id);

        return $this->jsonResponse(
            UbicacionAlmacen::where('id_almacen', $id)->orderBy('pasillo')->orderBy('estante')->get()
        );
    }

    public function storeUbicacion(Request $request, $id)
    {
        $almacen = Almacen::findOrFail($id);

        $data = $request->validate([
            'pasillo' => 'required|string|max:20',
            'estante' => 'required|string|max:20',
        ]);

        $exists = UbicacionAlmacen::where('id_almacen', $id)
            ->where('pasillo', $data['pasillo'])
            ->where('estante', $data['estante'])
            ->exists();

        if ($exists) {
            return $this->errorResponse('Ya existe una ubicación con ese pasillo y estante en este almacén.', 422);
        }

        $data['id_almacen'] = $almacen->id_almacen;

        $ubicacion = UbicacionAlmacen::create($data);

        return $this->jsonResponse($ubicacion, 'Ubicación agregada exitosamente.', 201);
    }

    public function updateUbicacion(Request $request, $id, $ubicacionId)
    {
        $almacen = Almacen::findOrFail($id);
        $ubicacion = UbicacionAlmacen::where('id_almacen', $id)->findOrFail($ubicacionId);

        $data = $request->validate([
            'pasillo' => 'sometimes|string|max:20',
            'estante' => 'sometimes|string|max:20',
        ]);

        $pasillo = $data['pasillo'] ?? $ubicacion->pasillo;
        $estante = $data['estante'] ?? $ubicacion->estante;

        $exists = UbicacionAlmacen::where('id_almacen', $id)
            ->where('pasillo', $pasillo)
            ->where('estante', $estante)
            ->where('id_ubicacion', '!=', $ubicacionId)
            ->exists();

        if ($exists) {
            return $this->errorResponse('Ya existe una ubicación con ese pasillo y estante en este almacén.', 422);
        }

        $ubicacion->update($data);

        return $this->jsonResponse($ubicacion, 'Ubicación actualizada exitosamente.');
    }

    public function destroyUbicacion($id, $ubicacionId)
    {
        $almacen = Almacen::findOrFail($id);
        $ubicacion = UbicacionAlmacen::where('id_almacen', $id)->findOrFail($ubicacionId);

        try {
            $ubicacion->delete();
            return $this->jsonResponse(null, 'Ubicación eliminada exitosamente.');
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                return $this->errorResponse('No se puede eliminar la ubicación porque tiene inventario asociado.', 409);
            }
            throw $e;
        }
    }
}
