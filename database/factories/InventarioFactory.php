<?php

namespace Database\Factories;

use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventarioFactory extends Factory
{
    protected $model = Inventario::class;

    public function definition()
    {
        return [
            'producto_id' => Producto::inRandomOrder()->first()->id ?? Producto::factory(),
            'lote_id' => Lote::inRandomOrder()->first()->id ?? Lote::factory(),
            'stock_actual' => $this->faker->numberBetween(0, 1000),
            'stock_minimo' => $this->faker->numberBetween(5, 50),
            'ubicacion' => 'Estante '.$this->faker->randomLetter().'-'.$this->faker->numberBetween(1, 20),
        ];
    }
}
