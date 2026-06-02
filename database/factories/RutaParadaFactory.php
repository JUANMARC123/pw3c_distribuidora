<?php

namespace Database\Factories;

use App\Models\Farmacia;
use App\Models\Ruta;
use App\Models\RutaParada;
use Illuminate\Database\Eloquent\Factories\Factory;

class RutaParadaFactory extends Factory
{
    protected $model = RutaParada::class;

    public function definition()
    {
        return [
            'ruta_id' => Ruta::inRandomOrder()->first()->id ?? Ruta::factory(),
            'farmacia_id' => Farmacia::inRandomOrder()->first()->id ?? Farmacia::factory(),
            'orden_parada' => $this->faker->unique()->numberBetween(1, 100),
        ];
    }
}
