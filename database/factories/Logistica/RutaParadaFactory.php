<?php

namespace Database\Factories\Logistica;

use App\Models\Logistica\RutaParada;
use Illuminate\Database\Eloquent\Factories\Factory;

class RutaParadaFactory extends Factory
{
    protected $model = RutaParada::class;

    public function definition()
    {
        return [
            'id_ruta' => 1,
            'id_farmacia' => 1,
            'orden_parada' => 1,
            'hora_estimada' => fake()->time('H:i:s'),
        ];
    }
}
