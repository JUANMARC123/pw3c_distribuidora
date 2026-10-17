<?php

namespace Database\Factories;

use App\Models\CajaRepartidor;
use App\Models\GastoOperativo;
use Illuminate\Database\Eloquent\Factories\Factory;

class GastoOperativoFactory extends Factory
{
    protected $model = GastoOperativo::class;

    public function definition()
    {
        $tipos = ['COMBUSTIBLE', 'ALIMENTACION', 'PEAJE', 'MANTENIMIENTO', 'IMPLEMENTOS', 'OTRO'];
        return [
            'id_caja' => CajaRepartidor::factory(),
            'tipo_gasto' => $this->faker->randomElement($tipos),
            'descripcion' => $this->faker->optional()->sentence(),
            'monto' => $this->faker->randomFloat(2, 10, 300),
            'comprobante' => $this->faker->optional()->uuid() . '.pdf',
            'fecha' => now(),
        ];
    }
}
