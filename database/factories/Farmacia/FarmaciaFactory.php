<?php

namespace Database\Factories\Farmacia;

use App\Models\Farmacia\Farmacia;
use Illuminate\Database\Eloquent\Factories\Factory;

class FarmaciaFactory extends Factory
{
    protected $model = Farmacia::class;

    public function definition()
    {
        return [
            'nombre' => 'Farmacia ' . fake()->company(),
            'direccion' => fake()->address(),
            'telefono' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'latitud' => fake()->latitude(-22, -16),
            'longitud' => fake()->longitude(-68, -62),
        ];
    }
}
