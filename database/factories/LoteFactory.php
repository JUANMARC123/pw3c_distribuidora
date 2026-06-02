<?php

namespace Database\Factories;

use App\Models\Lote;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoteFactory extends Factory
{
    protected $model = Lote::class;

    public function definition()
    {
        $fabricacion = $this->faker->dateTimeBetween('-2 years', '-3 months');
        $vencimiento = (clone $fabricacion)->modify('+'.$this->faker->numberBetween(12, 36).' months');

        return [
            'producto_id' => Producto::inRandomOrder()->first()->id ?? Producto::factory(),
            'numero_lote' => strtoupper($this->faker->unique()->bothify('LT-#####-##')),
            'fecha_fabricacion' => $fabricacion,
            'fecha_vencimiento' => $vencimiento,
        ];
    }
}
