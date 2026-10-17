<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CatalogoSeeder extends Seeder
{
    public function run()
    {
        $modules = [
            'Dashboard' => ['acceder'],
            'Usuarios' => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'asignar-roles'],
            'Roles' => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'asignar-permisos'],
            'Farmacias' => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'gestionar-contactos'],
            'Pedidos' => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'cambiar-estado'],
            'Productos' => ['acceder', 'listar', 'crear', 'editar', 'eliminar'],
            'Repartidores' => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'cambiar-estado'],
            'Vehículos' => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'cambiar-estado'],
            'Rutas' => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'gestionar-paradas'],
            'Control Rutas' => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'registrar-llegada'],
            'Despachos' => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'cambiar-estado', 'gestionar-evidencias'],
            'Reportes' => ['acceder', 'listar'],
            'Caja Diaria' => ['acceder', 'listar', 'crear', 'editar', 'cerrar'],
            'Documentos' => ['acceder'],
        ];

        foreach ($modules as $module => $acciones) {
            foreach ($acciones as $accion) {
                Permission::findOrCreate("{$module}-{$accion}", 'web');
            }
        }

        $admin = Role::findOrCreate('Administrador', 'web');
        $admin->givePermissionTo(Permission::all());

        $supervisor = Role::findOrCreate('Supervisor', 'web');
        $supervisor->givePermissionTo(Permission::whereIn('name', [
            'Dashboard-acceder',
            'Farmacias-acceder', 'Farmacias-listar', 'Farmacias-crear', 'Farmacias-editar',
            'Pedidos-acceder', 'Pedidos-listar', 'Pedidos-crear', 'Pedidos-editar', 'Pedidos-cambiar-estado',
            'Productos-acceder', 'Productos-listar', 'Productos-crear', 'Productos-editar',
            'Repartidores-acceder', 'Repartidores-listar',
            'Vehículos-acceder', 'Vehículos-listar',
            'Rutas-acceder', 'Rutas-listar', 'Rutas-crear', 'Rutas-editar', 'Rutas-gestionar-paradas',
            'Control Rutas-acceder', 'Control Rutas-listar', 'Control Rutas-crear', 'Control Rutas-editar',
            'Despachos-acceder', 'Despachos-listar', 'Despachos-crear', 'Despachos-editar', 'Despachos-cambiar-estado',
            'Reportes-acceder', 'Reportes-listar',
            'Caja Diaria-acceder', 'Caja Diaria-listar',
        ])->get());

        $operador = Role::findOrCreate('Operador', 'web');
        $operador->givePermissionTo(Permission::whereIn('name', [
            'Dashboard-acceder',
            'Farmacias-acceder', 'Farmacias-listar',
            'Pedidos-acceder', 'Pedidos-listar', 'Pedidos-crear', 'Pedidos-cambiar-estado',
            'Repartidores-acceder', 'Repartidores-listar',
            'Vehículos-acceder', 'Vehículos-listar',
            'Rutas-acceder', 'Rutas-listar',
            'Control Rutas-acceder', 'Control Rutas-listar',
            'Despachos-acceder', 'Despachos-listar', 'Despachos-crear', 'Despachos-cambiar-estado',
            'Reportes-acceder', 'Reportes-listar',
        ])->get());

        $repartidorRole = Role::findOrCreate('Repartidor', 'web');
        $repartidorRole->givePermissionTo(Permission::whereIn('name', [
            'Dashboard-acceder',
            'Despachos-acceder', 'Despachos-listar', 'Despachos-cambiar-estado', 'Despachos-gestionar-evidencias',
            'Control Rutas-acceder', 'Control Rutas-listar', 'Control Rutas-registrar-llegada',
        ])->get());
    }
}
