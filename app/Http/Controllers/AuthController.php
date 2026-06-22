<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Seguridad\Usuario;
use App\Models\Seguridad\SesionUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    public function login(LoginRequest $request)
    {
        $user = Usuario::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        if ($user->id_estado_usuario !== 1) {
            return $this->errorResponse('Cuenta de usuario bloqueada o suspendida.', 403);
        }

        $user->ultimo_acceso = now();
        $user->save();

        SesionUsuario::create([
            'id_usuario' => $user->id_usuario,
            'fecha_inicio' => now(),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        $user->load('roles.permisos.modulo', 'roles.permisos.accion', 'estado');
        $permisos = $this->getPermisos($user);

        return $this->jsonResponse([
            'user' => $user,
            'token' => $token,
            'permisos' => $permisos,
        ], 'Inicio de sesión exitoso.');
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password_hash'] = Hash::make($data['password']);
        $data['fecha_creacion'] = now();
        $data['id_estado_usuario'] = 1;
        unset($data['password'], $data['roles']);

        $user = Usuario::create($data);

        if ($request->filled('roles')) {
            $user->roles()->attach($request->roles);
        } else {
            $user->roles()->attach(4);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        $user->load('roles.permisos.modulo', 'roles.permisos.accion', 'estado');
        $permisos = $this->getPermisos($user);

        return $this->jsonResponse([
            'user' => $user,
            'token' => $token,
            'permisos' => $permisos,
        ], 'Usuario registrado exitosamente.', 201);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        SesionUsuario::where('id_usuario', $user->id_usuario)
            ->whereNull('fecha_fin')
            ->latest('fecha_inicio')
            ->first()
            ?->update(['fecha_fin' => now()]);

        $user->currentAccessToken()->delete();

        return $this->jsonResponse(null, 'Sesión cerrada exitosamente.');
    }

    public function user(Request $request)
    {
        $user = $request->user()->load([
            'roles.permisos.modulo',
            'roles.permisos.accion',
            'estado',
        ]);

        $permisos = $this->getPermisos($user);

        return $this->jsonResponse([
            'user' => $user,
            'permisos' => $permisos,
        ]);
    }

    private function getPermisos($user): array
    {
        $seen = [];
        $result = [];
        foreach ($user->roles as $role) {
            foreach ($role->permisos as $permiso) {
                $id = $permiso->id_permiso;
                if (!isset($seen[$id])) {
                    $seen[$id] = true;
                    $result[] = [
                        'id_permiso' => $id,
                        'modulo' => $permiso->modulo->nombre,
                        'accion' => $permiso->accion->nombre,
                    ];
                }
            }
        }
        return $result;
    }
}
