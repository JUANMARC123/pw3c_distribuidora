<?php

namespace Database\Factories;

use App\Models\Despacho;
use App\Models\EvidenciaEntrega;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvidenciaEntregaFactory extends Factory
{
    protected $model = EvidenciaEntrega::class;

    public function definition()
    {
        return [
            'id_despacho' => Despacho::factory(),
            'tipo_evidencia' => $this->faker->randomElement(['firma', 'foto', 'sello']),
            'archivo' => 'evidencias/' . $this->faker->uuid() . '.jpg',
        ];
    }
}
