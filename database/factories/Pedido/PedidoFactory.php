<?php

namespace Database\Factories\Pedido;

use App\Models\Pedido\Pedido;
use Illuminate\Database\Eloquent\Factories\Factory;

class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    public function definition()
    {
        return [
            'id_farmacia' => 1,
            'id_usuario' => 1,
            'id_estado_pedido' => 1,
            'fecha_pedido' => now(),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
