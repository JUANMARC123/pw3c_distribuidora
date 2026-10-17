<?php

namespace App\Http\Requests\Seguridad;

use Illuminate\Foundation\Http\FormRequest;

class UsuarioStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|max:180|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'telefono' => 'required|string|max:20',
            'id_estado_usuario' => 'required|integer|exists:estados_usuario,id_estado_usuario',
            'roles' => 'sometimes|array',
            'roles.*' => 'integer|exists:roles,id_rol',
        ];
    }
}
