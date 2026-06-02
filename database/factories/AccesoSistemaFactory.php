<?php

namespace Database\Factories;

use App\Models\AccesoSistema;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccesoSistemaFactory extends Factory
{
    protected $model = AccesoSistema::class;

    public function definition()
    {
        $modulos = ['Dashboard', 'Farmacias', 'Productos', 'Despachos', 'Rutas', 'Inventario', 'Reportes', 'Usuarios', 'Configuración'];
        $acciones = ['ver', 'crear', 'editar', 'eliminar', 'exportar', 'imprimir'];

        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'modulo' => $this->faker->randomElement($modulos),
            'accion' => $this->faker->randomElement($acciones),
            'fecha' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
