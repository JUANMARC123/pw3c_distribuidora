<?php

namespace Database\Seeders;

use App\Models\ProductoPrincipio;
use Illuminate\Database\Seeder;

class ProductoPrincipioSeeder extends Seeder
{
    public function run()
    {
        $combos = [];
        $intentos = 0;
        while (count($combos) < 10 && $intentos < 100) {
            $p = \App\Models\Producto::inRandomOrder()->first()->id;
            $pa = \App\Models\PrincipioActivo::inRandomOrder()->first()->id;
            $key = $p.'-'.$pa;
            if (!in_array($key, $combos)) {
                ProductoPrincipio::create([
                    'producto_id' => $p,
                    'principio_activo_id' => $pa,
                    'cantidad' => \Illuminate\Support\Str::random(8),
                ]);
                $combos[] = $key;
            }
            $intentos++;
        }
    }
}
