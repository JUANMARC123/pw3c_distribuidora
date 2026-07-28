<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\ApiController;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolesController extends ApiController
{
    public function index()
    {
        $roles = Role::withCount('permissions')->orderBy('name')->get()->map(function ($role) {
            return [
                'id_rol' => $role->id,
                'nombre' => $role->name,
                'permisos_count' => $role->permissions_count,
            ];
        });

        return $this->jsonResponse($roles);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:roles,name',
        ]);

        $role = Role::create(['name' => $validated['nombre'], 'guard_name' => 'web']);

        return $this->jsonResponse([
            'id_rol' => $role->id,
            'nombre' => $role->name,
        ], 'Rol creado exitosamente.', 201);
    }

    public function show($id)
    {
        $role = Role::withCount('permissions')->findOrFail($id);

        return $this->jsonResponse([
            'id_rol' => $role->id,
            'nombre' => $role->name,
            'permisos_count' => $role->permissions_count,
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'nombre' => "required|string|max:255|unique:roles,name,{$id}",
        ]);

        $role->update(['name' => $validated['nombre']]);

        return $this->jsonResponse([
            'id_rol' => $role->id,
            'nombre' => $role->name,
        ], 'Rol actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        $assignedCount = DB::table('model_has_roles')->where('role_id', $id)->count();
        if ($assignedCount > 0) {
            return $this->errorResponse(
                'No se puede eliminar el rol porque está asignado a uno o más usuarios.',
                409
            );
        }

        $role->delete();

        return $this->jsonResponse(null, 'Rol eliminado exitosamente.');
    }

    public function permisos($id)
    {
        $role = Role::findOrFail($id);
        $assignedIds = $role->permissions->pluck('id');

        $allPermissions = Permission::orderBy('name')->get()->map(function ($perm) {
            $parts = explode('-', $perm->name, 2);
            return [
                'id_permiso' => $perm->id,
                'modulo' => ['nombre' => $parts[0] ?? 'General'],
                'accion' => ['nombre' => $parts[1] ?? $perm->name],
            ];
        });

        return $this->jsonResponse([
            'asignados' => $assignedIds,
            'permisos' => $allPermissions,
        ]);
    }

    public function asignarPermisos(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'permisos' => 'required|array',
            'permisos.*' => 'integer|exists:permissions,id',
        ]);

        $role->syncPermissions($validated['permisos']);

        return $this->jsonResponse(null, 'Permisos asignados exitosamente.');
    }
}
