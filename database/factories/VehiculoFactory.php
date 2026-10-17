<?php

namespace Database\Factories;

use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehiculoFactory extends Factory
{
    protected $model = Vehiculo::class;

    public function definition()
    {
        return [
            'placa' => strtoupper($this->faker->unique()->bothify('####???')),
            'marca' => $this->faker->randomElement(['Toyota', 'Suzuki', 'Nissan', 'Mitsubishi', 'Honda']),
            'modelo' => $this->faker->randomElement(['Hilux', 'Samurai', 'Frontier', 'L200', 'CR-V']),
            'estado' => 'disponible',
        ];
    }

    public function taller()
    {
        return $this->state(['estado' => 'taller']);
    }

    public function enRuta()
    {
        return $this->state(['estado' => 'en_ruta']);
    }
}
