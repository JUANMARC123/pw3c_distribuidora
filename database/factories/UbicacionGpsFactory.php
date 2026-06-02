<?php

namespace Database\Factories;

use App\Models\Repartidor;
use App\Models\UbicacionGps;
use Illuminate\Database\Eloquent\Factories\Factory;

class UbicacionGpsFactory extends Factory
{
    protected $model = UbicacionGps::class;

    public function definition()
    {
        $citiesCoords = [
            ['lat' => -17.3935, 'lng' => -66.1570],
            ['lat' => -16.5000, 'lng' => -68.1500],
            ['lat' => -17.7833, 'lng' => -63.1821],
        ];
        $coord = $this->faker->randomElement($citiesCoords);

        return [
            'repartidor_id' => Repartidor::inRandomOrder()->first()->id ?? Repartidor::factory(),
            'latitud' => $coord['lat'] + $this->faker->randomFloat(7, -0.1, 0.1),
            'longitud' => $coord['lng'] + $this->faker->randomFloat(7, -0.1, 0.1),
            'velocidad' => $this->faker->randomFloat(2, 0, 120),
            'fecha_hora' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
