<?php

namespace Database\Factories;

use App\Models\Farmacia;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    public function definition()
    {
        $subtotal = $this->faker->randomFloat(2, 100, 5000);
        $descuento = $this->faker->randomFloat(2, 0, $subtotal * 0.2);
        return [
            'id_farmacia' => Farmacia::factory(),
            'id_usuario' => Usuario::factory(),
            'estado' => 'pendiente',
            'fecha_pedido' => now()->subDays(rand(0, 30)),
            'observaciones' => $this->faker->optional()->sentence(),
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'total' => $subtotal - $descuento,
        ];
    }

    public function pendiente()
    {
        return $this->state(['estado' => 'pendiente']);
    }

    public function cancelado()
    {
        return $this->state(['estado' => 'cancelado']);
    }

    public function entregado()
    {
        return $this->state(['estado' => 'entregado']);
    }
}
