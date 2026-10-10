<?php

namespace Database\Seeders;

use App\Models\Farmacia;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class PedidoSeeder extends Seeder
{
    public function run()
    {
        $admin = Usuario::where('email', 'admin@example.com')->first();
        $farmacias = Farmacia::all();

        if ($farmacias->isEmpty()) return;

        for ($i = 0; $i < 10; $i++) {
            $farmacia = $farmacias[$i % $farmacias->count()];
            $subtotal = rand(100, 1000);

            Pedido::create([
                'id_farmacia' => $farmacia->id_farmacia,
                'id_usuario' => $admin?->id_usuario ?? 1,
                'estado' => 'pendiente',
                'fecha_pedido' => now()->startOfMonth()->addDays(rand(0, now()->day - 1))->setTime(rand(8, 17), rand(0, 59)),
                'observaciones' => 'Pedido generado por seeder',
                'subtotal' => $subtotal,
                'descuento' => 0,
                'total' => $subtotal,
            ]);
        }
    }
}
