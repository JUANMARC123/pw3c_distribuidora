<?php

namespace Database\Factories\Logistica;

use App\Models\Logistica\Ruta;
use Illuminate\Database\Eloquent\Factories\Factory;

class RutaFactory extends Factory
{
    protected $model = Ruta::class;

    public function definition()
    {
        return [
            'nombre_ruta' => fake()->unique()->city() . ' - ' . fake()->city(),
        ];
    }
}
