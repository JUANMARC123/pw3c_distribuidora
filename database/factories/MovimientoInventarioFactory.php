<?php

namespace Database\Factories;

use App\Models\Despacho;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovimientoInventarioFactory extends Factory
{
    protected $model = MovimientoInventario::class;

    public function definition()
    {
        return [
            'inventario_id' => Inventario::inRandomOrder()->first()->id ?? Inventario::factory(),
            'despacho_id' => $this->faker->boolean(70) ? (Despacho::inRandomOrder()->first()->id ?? Despacho::factory()) : null,
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'tipo_movimiento' => $this->faker->randomElement(['entrada', 'salida', 'ajuste', 'devolucion', 'merma']),
            'cantidad' => $this->faker->numberBetween(1, 100),
            'fecha' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
