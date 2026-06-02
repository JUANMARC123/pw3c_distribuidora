<?php

namespace Database\Factories;

use App\Models\Repartidor;
use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehiculoFactory extends Factory
{
    protected $model = Vehiculo::class;

    public function definition()
    {
        $marcas = [
            ['Toyota', ['Hilux', 'Corolla', 'Yaris']],
            ['Nissan', ['Frontier', 'Versa', 'Sentra']],
            ['Chevrolet', ['D-Max', 'NPR', 'N300']],
            ['Ford', ['Ranger', 'F-150', 'Transit']],
            ['Volkswagen', ['Amarok', 'Saveiro', 'Crafter']],
        ];
        $marca = $this->faker->randomElement($marcas);

        return [
            'repartidor_id' => Repartidor::inRandomOrder()->first()->id ?? Repartidor::factory(),
            'placa' => strtoupper($this->faker->unique()->bothify('???-####')),
            'marca' => $marca[0],
            'modelo' => $this->faker->randomElement($marca[1]).' '.$this->faker->numberBetween(2018, 2024),
        ];
    }
}
