<?php

namespace Database\Factories;

use App\Models\HojaRuta;
use App\Models\HojaRutaDetalle;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

class HojaRutaDetalleFactory extends Factory
{
    protected $model = HojaRutaDetalle::class;

    public function definition()
    {
        return [
            'id_hoja_ruta' => HojaRuta::factory(),
            'id_pedido' => Pedido::factory(),
            'orden_visita' => $this->faker->numberBetween(1, 20),
            'hora_llegada' => null,
            'hora_salida' => null,
        ];
    }
}
