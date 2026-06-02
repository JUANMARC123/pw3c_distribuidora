<?php

namespace Database\Seeders;

use App\Models\Inventario;
use Illuminate\Database\Seeder;

class InventarioSeeder extends Seeder
{
    public function run()
    {
        $combos = [];
        $intentos = 0;
        while (count($combos) < 10 && $intentos < 200) {
            $p = \App\Models\Producto::inRandomOrder()->first()->id;
            $l = \App\Models\Lote::inRandomOrder()->first()->id;
            $key = $p.'-'.$l;
            if (!in_array($key, $combos)) {
                Inventario::create([
                    'producto_id' => $p,
                    'lote_id' => $l,
                    'stock_actual' => rand(0, 1000),
                    'stock_minimo' => rand(5, 50),
                    'ubicacion' => 'Estante '.chr(65 + rand(0, 5)).'-'.rand(1, 20),
                ]);
                $combos[] = $key;
            }
            $intentos++;
        }
    }
}
