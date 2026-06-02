<?php

namespace Database\Seeders;

use App\Models\Repartidor;
use Illuminate\Database\Seeder;

class RepartidorSeeder extends Seeder
{
    public function run()
    {
        $usados = [];
        $intentos = 0;
        while (count($usados) < 10 && $intentos < 200) {
            $user = \App\Models\User::inRandomOrder()->first();
            if (!$user || in_array($user->id, $usados)) {
                $intentos++;
                continue;
            }
            Repartidor::factory()->create(['user_id' => $user->id]);
            $usados[] = $user->id;
        }
    }
}
