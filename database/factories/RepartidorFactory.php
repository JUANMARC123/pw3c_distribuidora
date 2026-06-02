<?php

namespace Database\Factories;

use App\Models\Repartidor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RepartidorFactory extends Factory
{
    protected $model = Repartidor::class;

    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'licencia' => strtoupper($this->faker->unique()->bothify('LIC-######')),
            'tipo_licencia' => $this->faker->randomElement(['A', 'B', 'C', 'M']),
            'vencimiento_licencia' => $this->faker->dateTimeBetween('+6 months', '+5 years'),
        ];
    }
}
