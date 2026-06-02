<?php

namespace Database\Factories;

use App\Models\PrincipioActivo;
use App\Models\Producto;
use App\Models\ProductoPrincipio;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoPrincipioFactory extends Factory
{
    protected $model = ProductoPrincipio::class;

    public function definition()
    {
        return [
            'producto_id' => Producto::inRandomOrder()->first()->id ?? Producto::factory(),
            'principio_activo_id' => PrincipioActivo::inRandomOrder()->first()->id ?? PrincipioActivo::factory(),
            'cantidad' => $this->faker->randomElement(['100mg', '200mg', '500mg', '1g', '5mg/ml', '10mg/ml']),
        ];
    }
}
