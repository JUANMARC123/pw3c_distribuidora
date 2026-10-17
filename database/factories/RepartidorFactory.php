<?php

namespace Database\Factories;

use App\Models\Repartidor;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class RepartidorFactory extends Factory
{
    protected $model = Repartidor::class;

    public function definition()
    {
        return [
            'id_usuario' => Usuario::factory(),
            'ci' => $this->faker->unique()->numerify('########'),
            'categoria_licencia' => $this->faker->randomElement(['A', 'B', 'C']),
            'estado' => 'activo',
            'firebase_uid' => $this->faker->unique()->uuid(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Repartidor $repartidor) {
            if (!$repartidor->usuario->hasRole('Repartidor')) {
                $repartidor->usuario->assignRole('Repartidor');
            }
        });
    }
}
