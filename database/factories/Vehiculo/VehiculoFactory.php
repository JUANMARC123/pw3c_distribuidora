<?php

namespace Database\Factories\Vehiculo;

use App\Models\Vehiculo\Vehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehiculoFactory extends Factory
{
    protected $model = Vehiculo::class;

    public function definition()
    {
        return [
            'placa' => strtoupper(fake()->bothify('????-###')),
            'id_modelo' => fake()->numberBetween(1, 10),
            'id_capacidad' => fake()->numberBetween(1, 5),
            'id_estado_vehiculo' => 1,
        ];
    }
}
