<?php

namespace Database\Factories;

use App\Models\SesionUsuario;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SesionUsuarioFactory extends Factory
{
    protected $model = SesionUsuario::class;

    public function definition()
    {
        $inicio = $this->faker->dateTimeBetween('-30 days', 'now');
        $fin = (clone $inicio)->modify('+'.$this->faker->numberBetween(5, 240).' minutes');

        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'fecha_inicio' => $inicio,
            'fecha_fin' => $this->faker->boolean(80) ? $fin : null,
        ];
    }
}
