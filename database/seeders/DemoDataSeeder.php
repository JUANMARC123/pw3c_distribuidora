<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Repartidor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        $admin = Usuario::create([
            'nombre' => 'Admin',
            'apellido' => 'Sistema',
            'email' => 'admin@example.com',
            'password_hash' => Hash::make('123456'),
            'telefono' => '70000000',
            'estado' => 'activo',
        ]);
        $admin->assignRole('Administrador');

        $supervisor = Usuario::create([
            'nombre' => 'Supervisor',
            'apellido' => 'Principal',
            'email' => 'supervisor@example.com',
            'password_hash' => Hash::make('123456'),
            'telefono' => '70000001',
            'estado' => 'activo',
        ]);
        $supervisor->assignRole('Supervisor');

        $operador = Usuario::create([
            'nombre' => 'Operador',
            'apellido' => 'General',
            'email' => 'operador@example.com',
            'password_hash' => Hash::make('123456'),
            'telefono' => '70000002',
            'estado' => 'activo',
        ]);
        $operador->assignRole('Operador');

        $repartidorUser = Usuario::create([
            'nombre' => 'Repartidor',
            'apellido' => 'Uno',
            'email' => 'repartidor@example.com',
            'password_hash' => Hash::make('123456'),
            'telefono' => '70000003',
            'estado' => 'activo',
        ]);
        $repartidorUser->assignRole('Repartidor');

        Repartidor::create([
            'id_usuario' => $repartidorUser->id_usuario,
            'ci' => '12345678',
            'categoria_licencia' => 'B',
            'estado' => 'activo',
            'firebase_uid' => 'repartidor-demo-001',
        ]);
    }
}
