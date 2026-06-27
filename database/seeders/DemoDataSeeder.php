<?php

namespace Database\Seeders;

use App\Models\Repartidor\Repartidor;
use App\Models\Seguridad\Usuario;
use App\Models\Seguridad\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        $roles = Rol::pluck('id_rol', 'nombre');

        $demoUsers = [
            ['nombre' => 'Admin', 'apellido' => 'Sistema', 'email' => 'admin@distribuidora.com', 'rol' => 'Administrador'],
            ['nombre' => 'Carlos', 'apellido' => 'Mendoza', 'email' => 'supervisor@distribuidora.com', 'rol' => 'Supervisor'],
            ['nombre' => 'Lucía', 'apellido' => 'Paredes', 'email' => 'operador@distribuidora.com', 'rol' => 'Operador'],
            ['nombre' => 'Pedro', 'apellido' => 'Quispe', 'email' => 'repartidor@distribuidora.com', 'rol' => 'Repartidor'],
        ];

        foreach ($demoUsers as $userData) {
            $usuario = Usuario::create([
                'nombre' => $userData['nombre'],
                'apellido' => $userData['apellido'],
                'email' => $userData['email'],
                'password_hash' => Hash::make('123456'),
                'telefono' => fake()->phoneNumber(),
                'id_estado_usuario' => 1,
                'fecha_creacion' => now(),
            ]);
            $usuario->roles()->attach($roles[$userData['rol']]);

            if ($userData['rol'] === 'Repartidor') {
                Repartidor::create([
                    'id_usuario' => $usuario->id_usuario,
                    'id_estado_repartidor' => 1,
                    'ci' => (string) fake()->unique()->numberBetween(1000000, 9999999),
                    'id_extension_ci' => fake()->numberBetween(1, 9),
                    'id_licencia' => fake()->numberBetween(1, 5),
                ]);
            }
        }
    }
}
