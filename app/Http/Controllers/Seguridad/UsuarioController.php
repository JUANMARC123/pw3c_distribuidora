<?php

namespace App\Http\Controllers\Seguridad;
use Illuminate\Database\QueryException;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Seguridad\UsuarioStoreRequest;
use App\Models\Seguridad\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends ApiController
{
    public function index(Request $request)
    {
        $query = Usuario::with('estado', 'roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('id_estado_usuario')) {
            $query->where('id_estado_usuario', $request->id_estado_usuario);
        }

        return $this->paginatedResponse(
            $query->orderBy('id_usuario', 'desc')->paginate($request->per_page ?? 15)
        );
    }

    public function store(UsuarioStoreRequest $request)
    {
        $data = $request->validated();
        $data['password_hash'] = Hash::make($data['password']);
        $data['fecha_creacion'] = now();
        unset($data['password']);

        $usuario = Usuario::create($data);

        if ($request->filled('roles')) {
            $usuario->roles()->attach($request->roles);
        }

        return $this->jsonResponse(
            $usuario->load('estado', 'roles'),
            'Usuario creado exitosamente.',
            201
        );
    }

    public function show($id)
    {
        $usuario = Usuario::with('estado', 'roles', 'repartidor')->findOrFail($id);
        return $this->jsonResponse($usuario);
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $rules = [
            'nombre' => 'sometimes|string|max:100',
            'apellido' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:180|unique:usuarios,email,' . $id . ',id_usuario',
            'telefono' => 'sometimes|string|max:20',
            'id_estado_usuario' => 'sometimes|integer|exists:estados_usuario,id_estado_usuario',
            'password' => 'sometimes|string|min:6',
            'roles' => 'sometimes|array',
            'roles.*' => 'integer|exists:roles,id_rol',
        ];

        $data = $request->validate($rules);

        if (isset($data['password'])) {
            $data['password_hash'] = Hash::make($data['password']);
            unset($data['password']);
        }

        $usuario->update($data);

        if ($request->has('roles')) {
            $usuario->roles()->sync($request->roles);
        }

        return $this->jsonResponse(
            $usuario->load('estado', 'roles'),
            'Usuario actualizado exitosamente.'
        );
    }

    public function destroy($id)
{
    $usuario = Usuario::findOrFail($id);

    try {
        $usuario->delete();

        return $this->jsonResponse(null, 'Usuario eliminado exitosamente.');
    } catch (QueryException $e) {
        if ($e->getCode() == 23000) {
            return response()->json([
                'success' => false,
                'can_block' => true,
                'message' => 'No se puede eliminar este usuario porque tiene pedidos registrados.'
            ], 409);
        }

        throw $e;
    }
}

public function bloquear($id)
{
    $usuario = Usuario::findOrFail($id);

    $usuario->update([
        'id_estado_usuario' => 2 // Bloqueado
    ]);

    return $this->jsonResponse(
        $usuario->load('estado', 'roles'),
        'Usuario bloqueado exitosamente.'
    );
}

    public function roles($id)
    {
        $usuario = Usuario::findOrFail($id);
        return $this->jsonResponse($usuario->roles);
    }

    public function assignRoles(Request $request, $id)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'integer|exists:roles,id_rol',
        ]);

        $usuario = Usuario::findOrFail($id);
        $usuario->roles()->sync($request->roles);

        return $this->jsonResponse(
            $usuario->roles,
            'Roles asignados exitosamente.'
        );
    }
}
