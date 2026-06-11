<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Seguridad\Usuario;
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

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->jsonResponse([
            'user' => $user->load('roles', 'estado'),
            'token' => $token,
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

        return $this->jsonResponse([
            'user' => $user->load('roles', 'estado'),
            'token' => $token,
        ], 'Usuario registrado exitosamente.', 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->jsonResponse(null, 'Sesión cerrada exitosamente.');
    }

    public function user(Request $request)
    {
        return $this->jsonResponse(
            $request->user()->load('roles', 'estado')
        );
    }
}
