<?php

namespace Database\Factories\Logistica;

use App\Models\Logistica\ControlRuta;
use Illuminate\Database\Eloquent\Factories\Factory;

class ControlRutaFactory extends Factory
{
    protected $model = ControlRuta::class;

    public function definition()
    {
        return [
            'id_ruta' => 1,
            'fecha_ruta' => fake()->dateTimeBetween('-1 month', 'now'),
            'hora_salida' => fake()->time('H:i:s'),
            'hora_llegada_real' => fake()->optional()->time('H:i:s'),
            'id_repartidor' => 1,
            'id_vehiculo' => 1,
        ];
    }
}
