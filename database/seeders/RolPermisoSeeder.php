<?php

namespace Database\Seeders;

use App\Models\RolPermiso;
use Illuminate\Database\Seeder;

class RolPermisoSeeder extends Seeder
{
    public function run()
    {
        $combos = [];
        $attempts = 0;
        while (count($combos) < 10 && $attempts < 100) {
            $combo = [
                'rol_id' => \App\Models\Rol::inRandomOrder()->first()->id,
                'permiso_id' => \App\Models\Permiso::inRandomOrder()->first()->id,
            ];
            $key = $combo['rol_id'].'-'.$combo['permiso_id'];
            if (!in_array($key, array_column($combos, 'key'))) {
                $combo['key'] = $key;
                $combos[] = $combo;
            }
            $attempts++;
        }
        foreach ($combos as $c) {
            RolPermiso::create(['rol_id' => $c['rol_id'], 'permiso_id' => $c['permiso_id']]);
        }
    }
}
