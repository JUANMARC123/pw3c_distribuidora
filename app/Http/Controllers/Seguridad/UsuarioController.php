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

        return $this->safePaginate($query->orderBy('id_usuario', 'desc'), $request);
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
        ]);

        $validated['password_hash'] = Hash::make($validated['password']);
        unset($validated['password']);
        $validated['estado'] = $validated['estado'] ?? 'activo';

        $usuario = Usuario::create($validated);

        if ($request->filled('roles')) {
            $usuario->syncRoles($request->roles);
        } else {
            $usuario->assignRole('Operador');
        }

        $usuario->load('roles');

        return $this->jsonResponse($usuario, 'Usuario creado exitosamente.', 201);
    }

    public function show($id)
    {
        $usuario = Usuario::with('roles', 'repartidor')->findOrFail($id);
        return $this->jsonResponse($usuario);
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
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6';
        }

        $validated = $request->validate($rules);

        if ($request->filled('password')) {
            $validated['password_hash'] = Hash::make($validated['password']);
            unset($validated['password']);
        }

        $usuario->update($validated);

        if ($request->filled('roles')) {
            $usuario->syncRoles($request->roles);
        }

        $usuario->load('roles');

        return $this->jsonResponse($usuario, 'Usuario actualizado exitosamente.');
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
}
