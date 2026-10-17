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
            'permisos' => 'sometimes|array',
            'permisos.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::create(['name' => $validated['nombre'], 'guard_name' => 'web']);

        if (!empty($validated['permisos'])) {
            $role->syncPermissions($validated['permisos']);
        }

        return $this->jsonResponse([
            'id_rol' => $role->id,
            'nombre' => $role->name,
            'permisos_count' => $role->permissions()->count(),
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

        return $this->jsonResponse([
            'asignados' => $role->permissions->pluck('id'),
            'permisos' => $this->catalogoPermisos(),
        ]);
    }

    /**
     * Catálogo completo de permisos, independiente de un rol.
     * Se usa para poder asignar permisos al crear un rol nuevo.
     */
    public function permisosCatalogo()
    {
        return $this->jsonResponse([
            'asignados' => [],
            'permisos' => $this->catalogoPermisos(),
        ]);
    }

    /** Permisos agrupables por módulo, en la forma que consume el front-end. */
    private function catalogoPermisos()
    {
        return Permission::orderBy('name')->get()->map(function ($perm) {
            $parts = explode('-', $perm->name, 2);

            return [
                'id_permiso' => $perm->id,
                'modulo' => ['nombre' => $parts[0] ?? 'General'],
                'accion' => ['nombre' => $parts[1] ?? $perm->name],
            ];
        });
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
