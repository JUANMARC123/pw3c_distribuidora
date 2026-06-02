<?php

namespace Database\Factories;

use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditoriaFactory extends Factory
{
    protected $model = Auditoria::class;

    public function definition()
    {
        $tablas = ['farmacias', 'productos', 'despachos', 'usuarios', 'inventarios', 'rutas', 'repartidores'];
        $acciones = ['CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'EXPORT'];
        $tabla = $this->faker->randomElement($tablas);

        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'accion' => $this->faker->randomElement($acciones),
            'tabla_afectada' => $tabla,
            'registro_id' => $this->faker->numberBetween(1, 100),
        ];
    }
}
