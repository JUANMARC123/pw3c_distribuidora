<?php

namespace Database\Factories;

use App\Models\Farmacia;
use Illuminate\Database\Eloquent\Factories\Factory;

class FarmaciaFactory extends Factory
{
    protected $model = Farmacia::class;

    protected $centrosElAlto = [
        [-16.5186, -68.1972], // Villa Alemania
        [-16.5235, -68.1908], // Villa Adela
    ];

    public function definition()
    {
        [$lat, $lon] = $this->centrosElAlto[array_rand($this->centrosElAlto)];

        return [
            'nombre' => $this->faker->company(),
            'direccion' => $this->faker->streetAddress(),
            'latitud' => $lat + $this->faker->randomFloat(7, -0.006, 0.006),
            'longitud' => $lon + $this->faker->randomFloat(7, -0.006, 0.006),
            'geocerca' => 20,
        ];
    }
}
