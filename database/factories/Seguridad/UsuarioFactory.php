<?php

namespace Database\Factories\Seguridad;

use App\Models\Seguridad\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition()
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password_hash' => Hash::make('123456'),
            'telefono' => fake()->phoneNumber(),
            'id_estado_usuario' => 1,
            'fecha_creacion' => now(),
        ];
    }
}
