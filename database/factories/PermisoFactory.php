<?php

namespace Database\Factories;

use App\Models\Permiso;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermisoFactory extends Factory
{
    protected $model = Permiso::class;

    public function definition()
    {
        $modulos = ['Usuarios', 'Roles', 'Permisos', 'Farmacias', 'Productos', 'Inventario', 'Despachos', 'Rutas', 'Repartidores', 'Reportes'];
        $acciones = ['crear', 'editar', 'eliminar', 'ver', 'exportar'];
        $modulo = $this->faker->randomElement($modulos);
        $accion = $this->faker->randomElement($acciones);

        return [
            'nombre' => $accion.'_'.strtolower($modulo),
            'modulo' => $modulo,
            'descripcion' => 'Permite '.$accion.' registros en el módulo '.$modulo,
        ];
    }
}
