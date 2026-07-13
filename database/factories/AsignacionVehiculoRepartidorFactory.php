<?php

namespace Database\Factories;

use App\Models\AsignacionVehiculoRepartidor;
use App\Models\Repartidor;
use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

class AsignacionVehiculoRepartidorFactory extends Factory
{
    protected $model = AsignacionVehiculoRepartidor::class;

    public function definition()
    {
        return [
            'id_repartidor' => Repartidor::factory(),
            'id_vehiculo' => Vehiculo::factory(),
            'fecha_asignacion' => now(),
            'fecha_devolucion' => null,
        ];
    }

    public function devuelto()
    {
        return $this->state([
            'fecha_devolucion' => now()->subDays(rand(1, 30)),
        ]);
    }
}
