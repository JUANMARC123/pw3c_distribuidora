<?php

namespace Database\Factories;

use App\Models\Rol;
use App\Models\User;
use App\Models\UsuarioRol;
use Illuminate\Database\Eloquent\Factories\Factory;

class UsuarioRolFactory extends Factory
{
    protected $model = UsuarioRol::class;

    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'rol_id' => Rol::inRandomOrder()->first()->id ?? Rol::factory(),
        ];
    }
}
