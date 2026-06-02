<?php

namespace Database\Factories;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\RolPermiso;
use Illuminate\Database\Eloquent\Factories\Factory;

class RolPermisoFactory extends Factory
{
    protected $model = RolPermiso::class;

    public function definition()
    {
        return [
            'rol_id' => Rol::inRandomOrder()->first()->id ?? Rol::factory(),
            'permiso_id' => Permiso::inRandomOrder()->first()->id ?? Permiso::factory(),
        ];
    }
}
