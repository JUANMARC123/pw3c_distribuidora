<?php

namespace Database\Factories;

use App\Models\Despacho;
use App\Models\DetalleDespacho;
use App\Models\Inventario;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetalleDespachoFactory extends Factory
{
    protected $model = DetalleDespacho::class;

    public function definition()
    {
        return [
            'despacho_id' => Despacho::inRandomOrder()->first()->id ?? Despacho::factory(),
            'inventario_id' => Inventario::inRandomOrder()->first()->id ?? Inventario::factory(),
            'cantidad' => $this->faker->numberBetween(1, 50),
        ];
    }
}
