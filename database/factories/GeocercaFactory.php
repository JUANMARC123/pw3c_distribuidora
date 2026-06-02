<?php

namespace Database\Factories;

use App\Models\Geocerca;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GeocercaFactory extends Factory
{
    protected $model = Geocerca::class;

    public function definition()
    {
        $tipos = ['circular', 'poligono'];
        $tipo = $this->faker->randomElement($tipos);
        $lat = $this->faker->randomFloat(7, -20, -15);
        $lng = $this->faker->randomFloat(7, -69, -63);

        $coordenadas = $tipo === 'circular'
            ? [
                'centro' => ['lat' => $lat, 'lng' => $lng],
                'radio_metros' => $this->faker->numberBetween(100, 5000),
            ]
            : [
                'puntos' => [
                    ['lat' => $lat, 'lng' => $lng],
                    ['lat' => $lat + 0.01, 'lng' => $lng],
                    ['lat' => $lat + 0.01, 'lng' => $lng + 0.01],
                    ['lat' => $lat, 'lng' => $lng + 0.01],
                ],
            ];

        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'nombre' => $this->faker->randomElement(['Zona Centro', 'Zona Norte', 'Zona Sur', 'Zona Industrial', 'Zona Residencial', 'Zona Comercial']).' #'.$this->faker->numberBetween(1, 99),
            'tipo' => $tipo,
            'coordenadas' => $coordenadas,
        ];
    }
}
