<?php

namespace Database\Seeders;

use App\Models\UsuarioRol;
use Illuminate\Database\Seeder;

class UsuarioRolSeeder extends Seeder
{
    public function run()
    {
        $combos = [];
        $attempts = 0;
        while (count($combos) < 10 && $attempts < 100) {
            $combo = [
                'user_id' => \App\Models\User::inRandomOrder()->first()->id,
                'rol_id' => \App\Models\Rol::inRandomOrder()->first()->id,
            ];
            $key = $combo['user_id'].'-'.$combo['rol_id'];
            if (!in_array($key, array_column($combos, 'key'))) {
                $combo['key'] = $key;
                $combos[] = $combo;
            }
            $attempts++;
        }
        foreach ($combos as $c) {
            UsuarioRol::create(['user_id' => $c['user_id'], 'rol_id' => $c['rol_id']]);
        }
    }
}
