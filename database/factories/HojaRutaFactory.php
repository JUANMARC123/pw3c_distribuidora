<?php

namespace Database\Factories;

use App\Models\HojaRuta;
use App\Models\Repartidor;
use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

class HojaRutaFactory extends Factory
{
    protected $model = HojaRuta::class;

    public function definition()
    {
        return [
            'fecha' => now()->format('Y-m-d'),
            'hora_salida' => null,
            'hora_llegada' => null,
            'id_repartidor' => Repartidor::factory(),
            'id_vehiculo' => Vehiculo::factory(),
            'estado_ruta' => 'planificado',
        ];
    }

    public function enProgreso()
    {
        return $this->state([
            'estado_ruta' => 'en_progreso',
            'hora_salida' => now()->format('H:i:s'),
        ]);
    }

    public function completado()
    {
        return $this->state([
            'estado_ruta' => 'completado',
            'hora_salida' => now()->subHours(4)->format('H:i:s'),
            'hora_llegada' => now()->format('H:i:s'),
        ]);
    }

    public function cancelado()
    {
        return $this->state(['estado_ruta' => 'cancelado']);
    }
}
