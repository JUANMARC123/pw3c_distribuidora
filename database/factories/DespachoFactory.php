<?php

namespace Database\Factories;

use App\Models\Despacho;
use App\Models\HojaRutaDetalle;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

class DespachoFactory extends Factory
{
    protected $model = Despacho::class;

    public function definition()
    {
        return [
            'id_pedido' => Pedido::factory(),
            'id_detalle_hoja_ruta' => HojaRutaDetalle::factory(),
            'fecha_hora_despacho' => now(),
            'estado_despacho' => 'pendiente',
            'monto_recibido' => null,
        ];
    }

    public function entregado()
    {
        return $this->state([
            'estado_despacho' => 'entregado',
            'monto_recibido' => $this->faker->randomFloat(2, 50, 1000),
        ]);
    }

    public function incidencia()
    {
        return $this->state(['estado_despacho' => 'incidencia']);
    }
}
