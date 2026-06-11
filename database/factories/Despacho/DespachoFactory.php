<?php

namespace Database\Factories\Despacho;

use App\Models\Despacho\Despacho;
use Illuminate\Database\Eloquent\Factories\Factory;

class DespachoFactory extends Factory
{
    protected $model = Despacho::class;

    public function definition()
    {
        return [
            'id_pedido' => 1,
            'id_parada' => 1,
            'id_control_ruta' => 1,
            'fecha_hora_despacho' => now(),
            'id_estado_despacho' => 1,
        ];
    }
}
