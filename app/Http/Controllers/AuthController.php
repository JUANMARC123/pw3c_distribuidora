<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Usuario;
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

        if ($user->estado !== 'activo') {
            return $this->errorResponse('Cuenta de usuario bloqueada.', 403);
        }

        $user->save();

        $token = $user->createToken('api-token')->plainTextToken;

        $user->load('roles');
        $permisos = $user->getAllPermissions()->pluck('name');

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
        $data['estado'] = 'activo';
        unset($data['password']);

        $user = Usuario::create($data);

        $user->assignRole('Operador');

        $token = $user->createToken('api-token')->plainTextToken;

        $user->load('roles');
        $permisos = $user->getAllPermissions()->pluck('name');

        return $this->jsonResponse([
            'user' => $user,
            'token' => $token,
            'permisos' => $permisos,
        ], 'Usuario registrado exitosamente.', 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->jsonResponse(null, 'Sesión cerrada exitosamente.');
    }

    public function user(Request $request)
    {
        $user = $request->user()->load('roles');
        $permisos = $user->getAllPermissions()->pluck('name');

        return $this->jsonResponse([
            'user' => $user,
            'permisos' => $permisos,
        ]);
    }
}
