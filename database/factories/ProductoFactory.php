<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition()
    {
        $unidades = ['CAJA', 'BLISTER', 'TUBO', 'FRASCO', 'AMPOLLA', 'KG'];
        return [
            'nombre' => $this->faker->word() . ' ' . $this->faker->randomElement(['Genérico', 'Marca X', 'Fortex']),
            'precio_unitario' => $this->faker->randomFloat(2, 5, 500),
            'unidad_medida' => $this->faker->randomElement($unidades),
        ];
    }
}
