<?php

namespace Database\Factories;

use App\Models\Farmacia;
use Illuminate\Database\Eloquent\Factories\Factory;

class FarmaciaFactory extends Factory
{
    protected $model = Farmacia::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->company(),
            'direccion' => $this->faker->address(),
            'latitud' => $this->faker->latitude(-17, -16),
            'longitud' => $this->faker->longitude(-68, -67),
            'geocerca' => $this->faker->randomFloat(2, 50, 500),
        ];
    }
}
