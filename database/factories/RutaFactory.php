<?php

namespace Database\Factories;

use App\Models\Ruta;
use Illuminate\Database\Eloquent\Factories\Factory;

class RutaFactory extends Factory
{
    protected $model = Ruta::class;

    public function definition()
    {
        $estados = ['planificada', 'en_curso', 'completada', 'cancelada'];
        $estado = $this->faker->randomElement($estados);

        return [
            'codigo_ruta' => strtoupper($this->faker->unique()->bothify('RUTA-####-##')),
            'fecha' => $this->faker->dateTimeBetween('-30 days', '+15 days'),
            'distancia_total' => $this->faker->randomFloat(2, 5, 250),
            'tiempo_estimado' => $this->faker->numberBetween(30, 480),
            'estado' => $estado,
        ];
    }
}
