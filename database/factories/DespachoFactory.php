<?php

namespace Database\Factories;

use App\Models\Despacho;
use App\Models\Farmacia;
use App\Models\Repartidor;
use App\Models\Ruta;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DespachoFactory extends Factory
{
    protected $model = Despacho::class;

    public function definition()
    {
        $estados = ['pendiente', 'en_ruta', 'entregado', 'parcial', 'cancelado'];
        $estado = $this->faker->randomElement($estados);
        $salida = $this->faker->dateTimeBetween('-15 days', 'now');
        $entrega = in_array($estado, ['entregado', 'parcial'])
            ? (clone $salida)->modify('+'.$this->faker->numberBetween(1, 8).' hours')
            : null;

        return [
            'farmacia_id' => Farmacia::inRandomOrder()->first()->id ?? Farmacia::factory(),
            'repartidor_id' => Repartidor::inRandomOrder()->first()->id ?? Repartidor::factory(),
            'ruta_id' => Ruta::inRandomOrder()->first()->id ?? Ruta::factory(),
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'codigo_despacho' => strtoupper($this->faker->unique()->bothify('DSP-######')),
            'fecha_salida' => $salida,
            'fecha_entrega' => $entrega,
            'estado' => $estado,
        ];
    }
}
