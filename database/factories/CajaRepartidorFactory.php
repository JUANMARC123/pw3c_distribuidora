<?php

namespace Database\Factories;

use App\Models\CajaRepartidor;
use App\Models\HojaRuta;
use App\Models\Repartidor;
use Illuminate\Database\Eloquent\Factories\Factory;

class CajaRepartidorFactory extends Factory
{
    
    protected $model = CajaRepartidor::class;

    public function definition()
    {
        return [
            'id_repartidor' => Repartidor::factory(),
            'fecha' => now()->format('Y-m-d'),
            'ingresos' => 0,
            'monto_final' => null,
            'monto_inicial' => $this->faker->randomFloat(2, 50, 500),
            'estado_caja' => 'abierta',
        ];
    }

    public function cerrada()
    {
        return $this->state([
            'estado_caja' => 'cerrada',
            'monto_final' => $this->faker->randomFloat(2, 100, 1000),
        ]);
    }

    public function auditada()
    {
        return $this->state(['estado_caja' => 'auditada']);
    }
}
