<?php

namespace Database\Seeders;

use App\Models\DetallePedido;
use App\Models\Farmacia;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class PedidoSeeder extends Seeder
{
    public function run()
    {
        $admin = Usuario::where('email', 'admin@example.com')->first();
        $farmacias = Farmacia::all();
        $productos = Producto::all();

        if ($farmacias->isEmpty() || $productos->isEmpty()) return;

        for ($i = 0; $i < min(3, $farmacias->count()); $i++) {
            $farmacia = $farmacias[$i];
            $numProductos = rand(2, 5);
            $productosSeleccionados = $productos->random($numProductos);
            $subtotal = 0;
            $detalles = [];

            foreach ($productosSeleccionados as $producto) {
                $cantidad = rand(1, 10);
                $precio = $producto->precio_unitario;
                $subtotalLinea = $cantidad * $precio;
                $subtotal += $subtotalLinea;
                $detalles[] = [
                    'id_producto' => $producto->id_producto,
                    'cantidad_solicitada' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotalLinea,
                ];
            }

            $pedido = Pedido::create([
                'id_farmacia' => $farmacia->id_farmacia,
                'id_usuario' => $admin?->id_usuario ?? 1,
                'estado' => 'aprobado',
                'metodo_pago' => 'efectivo',
                'fecha_pedido' => now()->subDays(rand(0, 5)),
                'observaciones' => 'Pedido generado por seeder',
                'subtotal' => $subtotal,
                'descuento' => 0,
                'total' => $subtotal,
            ]);

            foreach ($detalles as $detalle) {
                DetallePedido::create([
                    'id_pedido' => $pedido->id_pedido,
                    'id_producto' => $detalle['id_producto'],
                    'cantidad_solicitada' => $detalle['cantidad_solicitada'],
                    'cantidad_entregada' => 0,
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['subtotal'],
                ]);
            }
        }
    }
}
