<?php

namespace Database\Seeders;

use App\Models\RutaParada;
use Illuminate\Database\Seeder;

class RutaParadaSeeder extends Seeder
{
    public function run()
    {
        $combos = [];
        $intentos = 0;
        while (count($combos) < 10 && $intentos < 200) {
            $r = \App\Models\Ruta::inRandomOrder()->first()->id;
            $f = \App\Models\Farmacia::inRandomOrder()->first()->id;
            $o = $intentos + 1;
            $key = $r.'-'.$f.'-'.$o;
            if (!in_array($key, $combos)) {
                RutaParada::create([
                    'ruta_id' => $r,
                    'farmacia_id' => $f,
                    'orden_parada' => $o,
                ]);
                $combos[] = $key;
            }
            $intentos++;
        }
    }
}
