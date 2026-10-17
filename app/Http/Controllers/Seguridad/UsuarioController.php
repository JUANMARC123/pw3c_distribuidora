<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\ApiController;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsuarioController extends ApiController
{
    public function index(Request $request)
    {
        $query = Usuario::with('roles');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellido', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $query->orderBy('id_usuario', 'desc');

        $perPage = $request->per_page ?? 15;

        if ($perPage == -1 || $perPage === '-1') {
            $usuarios = $query->get()->map(fn ($u) => $this->formatearUsuario($u));

            return $this->jsonResponse($usuarios);
        }

        $paginator = $query->paginate((int) $perPage);
        $paginator->through(fn ($u) => $this->formatearUsuario($u));

        return $this->paginatedResponse($paginator);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|max:180|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'telefono' => 'nullable|string|max:20',
            'estado' => 'sometimes|in:activo,bloqueado',
            'roles' => 'sometimes|array',
            'roles.*' => 'integer|exists:roles,id',
        ]);

        $roles = $validated['roles'] ?? null;
        unset($validated['roles']);

        $validated['password_hash'] = Hash::make($validated['password']);
        unset($validated['password']);
        $validated['estado'] = $validated['estado'] ?? 'activo';

        $usuario = Usuario::create($validated);

        if (!empty($roles)) {
            $usuario->syncRoles($roles);
        } else {
            $usuario->assignRole('Operador');
        }

        $usuario->load('roles');

        return $this->jsonResponse($this->formatearUsuario($usuario), 'Usuario creado exitosamente.', 201);
    }

    public function show($id)
    {
        $usuario = Usuario::with('roles', 'repartidor')->findOrFail($id);
        return $this->jsonResponse($this->formatearUsuario($usuario));
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $rules = [
            'nombre' => 'sometimes|string|max:100',
            'apellido' => 'sometimes|string|max:100',
            'email' => "sometimes|email|max:180|unique:usuarios,email,{$id},id_usuario",
            'telefono' => 'nullable|string|max:20',
            'estado' => 'sometimes|in:activo,bloqueado',
            'roles' => 'sometimes|array',
            'roles.*' => 'integer|exists:roles,id',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6';
        }

        $validated = $request->validate($rules);

        $roles = $validated['roles'] ?? null;
        unset($validated['roles']);

        if ($request->filled('password')) {
            $validated['password_hash'] = Hash::make($validated['password']);
            unset($validated['password']);
        }

        $usuario->update($validated);

        if ($roles !== null) {
            $usuario->syncRoles($roles);
        }

        $usuario->load('roles');

        return $this->jsonResponse($this->formatearUsuario($usuario), 'Usuario actualizado exitosamente.');
    }

    public function destroy($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);
            $usuario->delete();
            return $this->jsonResponse(null, 'Usuario eliminado exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse('No se puede eliminar el usuario porque tiene registros asociados.', 409);
        }
    }

    public function bloquear($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->update(['estado' => 'bloqueado']);
        return $this->jsonResponse($usuario, 'Usuario bloqueado exitosamente.');
    }

    /**
     * Serializa un usuario normalizando sus roles de Spatie a la forma que
     * consume el front-end ({ id_rol, nombre }) en lugar de { id, name }.
     */
    private function formatearUsuario(Usuario $usuario): array
    {
        $data = $usuario->toArray();
        $data['roles'] = $usuario->roles
            ->map(fn ($rol) => ['id_rol' => $rol->id, 'nombre' => $rol->name])
            ->values()
            ->all();

        return $data;
    }
}
