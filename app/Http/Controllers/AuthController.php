<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
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
