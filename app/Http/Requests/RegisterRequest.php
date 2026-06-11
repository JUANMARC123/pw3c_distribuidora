<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'roles' => 'sometimes|array',
            'roles.*' => 'integer|exists:roles,id_rol',
        ];
    }
}
