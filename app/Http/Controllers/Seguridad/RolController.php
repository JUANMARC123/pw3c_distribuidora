<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\ApiController;
use App\Models\Seguridad\Rol;
use Illuminate\Http\Request;

class RolController extends ApiController
{
    public function index()
    {
        return $this->jsonResponse(Rol::with('permisos')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50|unique:roles,nombre',
        ]);

        $rol = Rol::create($request->only('nombre'));

        return $this->jsonResponse($rol, 'Rol creado exitosamente.', 201);
    }

    public function show($id)
    {
        $rol = Rol::with('permisos.modulo', 'permisos.accion')->findOrFail($id);
        return $this->jsonResponse($rol);
    }

    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:50|unique:roles,nombre,' . $id . ',id_rol',
        ]);

        $rol->update($request->only('nombre'));

        return $this->jsonResponse($rol, 'Rol actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $rol = Rol::findOrFail($id);
        $rol->delete();

        return $this->jsonResponse(null, 'Rol eliminado exitosamente.');
    }

    public function permisos($id)
    {
        $rol = Rol::with('permisos.modulo', 'permisos.accion')->findOrFail($id);
        return $this->jsonResponse($rol->permisos);
    }

    public function assignPermisos(Request $request, $id)
    {
        $request->validate([
            'permisos' => 'required|array',
            'permisos.*' => 'integer|exists:permisos,id_permiso',
        ]);

        $rol = Rol::findOrFail($id);
        $rol->permisos()->sync($request->permisos);

        return $this->jsonResponse(
            $rol->permisos,
            'Permisos asignados exitosamente.'
        );
    }
}
