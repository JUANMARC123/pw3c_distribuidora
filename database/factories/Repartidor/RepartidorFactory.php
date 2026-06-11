<?php

namespace Database\Factories\Repartidor;

use App\Models\Repartidor\Repartidor;
use Illuminate\Database\Eloquent\Factories\Factory;

class RepartidorFactory extends Factory
{
    protected $model = Repartidor::class;

    public function definition()
    {
        return [
            'id_usuario' => 1,
            'ci' => fake()->unique()->numerify('########'),
            'id_extension_ci' => fake()->numberBetween(1, 9),
            'id_licencia' => fake()->numberBetween(1, 5),
            'id_estado_repartidor' => 1,
        ];
    }
}
