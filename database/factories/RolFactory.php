<?php

namespace Database\Factories;

use App\Models\Rol;
use Illuminate\Database\Eloquent\Factories\Factory;

class RolFactory extends Factory
{
    protected $model = Rol::class;

    public function definition()
    {
        $roles = [
            ['Administrador', 'Acceso total al sistema'],
            ['Operador', 'Gestiona despachos y rutas'],
            ['Repartidor', 'Realiza entregas y reportes GPS'],
            ['Supervisor', 'Monitorea operaciones y reportes'],
            ['Contador', 'Acceso a módulos financieros'],
            ['Vendedor', 'Gestiona clientes y pedidos'],
            ['Almacén', 'Administra inventario y lotes'],
            ['Auditor', 'Consulta logs y auditorías'],
            ['Soporte', 'Atención de incidencias'],
            ['Gerente', 'Visualización de indicadores'],
        ];
        $r = $this->faker->unique()->randomElement($roles);

        return [
            'nombre' => $r[0],
            'descripcion' => $r[1],
            'estado' => true,
        ];
    }
}
