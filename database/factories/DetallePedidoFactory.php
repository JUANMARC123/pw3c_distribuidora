<?php

namespace Database\Factories;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetallePedidoFactory extends Factory
{
    protected $model = DetallePedido::class;

    public function definition()
    {
        $cantidad = $this->faker->numberBetween(1, 100);
        $precio = $this->faker->randomFloat(2, 5, 200);
        return [
            'id_pedido' => Pedido::factory(),
            'id_producto' => Producto::factory(),
            'cantidad_solicitada' => $cantidad,
            'cantidad_entregada' => 0,
            'cantidad_rechazada' => 0,
            'precio_unitario' => $precio,
            'subtotal' => $cantidad * $precio,
        ];
    }

    public function entregado()
    {
        $cantidad = $this->faker->numberBetween(1, 100);
        $precio = $this->faker->randomFloat(2, 5, 200);
        return $this->state([
            'cantidad_solicitada' => $cantidad,
            'cantidad_entregada' => $cantidad,
            'subtotal' => $cantidad * $precio,
        ]);
    }
}
