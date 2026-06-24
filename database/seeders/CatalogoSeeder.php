<?php

namespace Database\Seeders;

use App\Models\Seguridad\EstadoUsuario;
use App\Models\Seguridad\Rol;
use App\Models\Seguridad\Modulo;
use App\Models\Seguridad\Accion;
use App\Models\Seguridad\TablaSistema;
use App\Models\Seguridad\Permiso;
use App\Models\Pedido\EstadoPedido;
use App\Models\Repartidor\EstadoRepartidor;
use App\Models\Repartidor\ExtensionCI;
use App\Models\Repartidor\Licencia;
use App\Models\Vehiculo\EstadoVehiculo;
use App\Models\Vehiculo\Marca;
use App\Models\Vehiculo\Modelo;
use App\Models\Vehiculo\Capacidad;
use App\Models\Despacho\EstadoDespacho;
use App\Models\Evidencia\TipoIncidencia;
use App\Models\Evidencia\TipoEvidencia;
use App\Models\Farmacia\Cargo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoSeeder extends Seeder
{
    private array $moduleActions = [
        'Dashboard'       => ['acceder'],
        'Reportes'        => ['acceder', 'listar'],
        'Usuarios'        => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'asignar-roles'],
        'Roles'           => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'asignar-permisos'],
        'Farmacias'       => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'gestionar-contactos'],
        'Pedidos'         => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'cambiar-estado'],
        'Repartidores'    => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'cambiar-estado'],
        'Vehículos'       => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'cambiar-estado'],
        'Rutas'           => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'gestionar-paradas'],
        'Control Rutas'   => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'registrar-llegada'],
        'Despachos'       => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'cambiar-estado', 'gestionar-incidencias', 'gestionar-evidencias'],
    ];

    public function run()
    {
        EstadoUsuario::insert([
            ['nombre_estado' => 'Activo'],
            ['nombre_estado' => 'Bloqueado'],
            ['nombre_estado' => 'Suspendido'],
        ]);

        Rol::insert([
            ['nombre' => 'Administrador'],
            ['nombre' => 'Supervisor'],
            ['nombre' => 'Operador'],
            ['nombre' => 'Repartidor'],
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Modulo::truncate();
        Accion::truncate();
        DB::table('rol_permiso')->truncate();
        Permiso::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach (array_keys($this->moduleActions) as $moduleName) {
            Modulo::create(['nombre' => $moduleName]);
        }

        $allActions = [];
        foreach ($this->moduleActions as $actions) {
            foreach ($actions as $action) {
                $allActions[$action] = true;
            }
        }

        foreach (array_keys($allActions) as $actionName) {
            Accion::create(['nombre' => $actionName]);
        }

        $modulos = Modulo::pluck('id_modulo', 'nombre');
        $acciones = Accion::pluck('id_accion', 'nombre');

        $permisosData = [];
        foreach ($this->moduleActions as $moduleName => $actions) {
            $idModulo = $modulos[$moduleName];
            foreach ($actions as $actionName) {
                $permisosData[] = [
                    'id_modulo' => $idModulo,
                    'id_accion' => $acciones[$actionName],
                ];
            }
        }
        Permiso::insertOrIgnore($permisosData);

        $allPermisos = Permiso::pluck('id_permiso')->toArray();

        $rolePermissions = [
            1 => null,
            2 => [
                ['Dashboard', 'acceder'],
                ['Reportes', 'acceder'], ['Reportes', 'listar'],
                ['Farmacias', 'acceder'], ['Farmacias', 'listar'], ['Farmacias', 'crear'], ['Farmacias', 'editar'], ['Farmacias', 'gestionar-contactos'],
                ['Pedidos', 'acceder'], ['Pedidos', 'listar'], ['Pedidos', 'crear'], ['Pedidos', 'editar'], ['Pedidos', 'cambiar-estado'],
                ['Repartidores', 'acceder'], ['Repartidores', 'listar'], ['Repartidores', 'crear'], ['Repartidores', 'editar'], ['Repartidores', 'cambiar-estado'],
                ['Vehículos', 'acceder'], ['Vehículos', 'listar'], ['Vehículos', 'crear'], ['Vehículos', 'editar'], ['Vehículos', 'cambiar-estado'],
                ['Rutas', 'acceder'], ['Rutas', 'listar'], ['Rutas', 'crear'], ['Rutas', 'editar'], ['Rutas', 'gestionar-paradas'],
                ['Control Rutas', 'acceder'], ['Control Rutas', 'listar'], ['Control Rutas', 'crear'], ['Control Rutas', 'editar'], ['Control Rutas', 'registrar-llegada'],
                ['Despachos', 'acceder'], ['Despachos', 'listar'], ['Despachos', 'crear'], ['Despachos', 'editar'], ['Despachos', 'cambiar-estado'], ['Despachos', 'gestionar-incidencias'], ['Despachos', 'gestionar-evidencias'],
            ],
            3 => [
                ['Dashboard', 'acceder'],
                ['Reportes', 'acceder'], ['Reportes', 'listar'],
                ['Farmacias', 'acceder'], ['Farmacias', 'listar'],
                ['Pedidos', 'acceder'], ['Pedidos', 'listar'], ['Pedidos', 'crear'], ['Pedidos', 'editar'], ['Pedidos', 'cambiar-estado'],
                ['Repartidores', 'acceder'], ['Repartidores', 'listar'],
                ['Vehículos', 'acceder'], ['Vehículos', 'listar'],
                ['Rutas', 'acceder'], ['Rutas', 'listar'],
                ['Control Rutas', 'acceder'], ['Control Rutas', 'listar'], ['Control Rutas', 'crear'], ['Control Rutas', 'editar'],
                ['Despachos', 'acceder'], ['Despachos', 'listar'], ['Despachos', 'crear'], ['Despachos', 'editar'], ['Despachos', 'cambiar-estado'],
            ],
            4 => [
                ['Dashboard', 'acceder'],
                ['Rutas', 'acceder'], ['Rutas', 'listar'],
                ['Control Rutas', 'acceder'], ['Control Rutas', 'listar'], ['Control Rutas', 'registrar-llegada'],
                ['Despachos', 'acceder'], ['Despachos', 'listar'],
            ],
        ];

        $permisoLookup = [];
        foreach (Permiso::with('modulo', 'accion')->get() as $permiso) {
            $key = $permiso->modulo->nombre . '::' . $permiso->accion->nombre;
            $permisoLookup[$key] = $permiso->id_permiso;
        }

        foreach ($rolePermissions as $rolId => $permisosRequeridos) {
            if ($permisosRequeridos === null) {
                $targetPermisos = $allPermisos;
            } else {
                $targetPermisos = [];
                foreach ($permisosRequeridos as [$modulo, $accion]) {
                    $key = $modulo . '::' . $accion;
                    if (isset($permisoLookup[$key])) {
                        $targetPermisos[] = $permisoLookup[$key];
                    }
                }
            }

            $existingAssignments = DB::table('rol_permiso')
                ->where('id_rol', $rolId)
                ->pluck('id_permiso')
                ->toArray();

            $newAssignments = array_diff($targetPermisos, $existingAssignments);

            if (!empty($newAssignments)) {
                $rolPermisoData = [];
                foreach ($newAssignments as $idPermiso) {
                    $rolPermisoData[] = [
                        'id_rol' => $rolId,
                        'id_permiso' => $idPermiso,
                    ];
                }
                DB::table('rol_permiso')->insertOrIgnore($rolPermisoData);
            }
        }

        TablaSistema::insertOrIgnore([
            ['nombre' => 'dashboard'],
            ['nombre' => 'reportes'],
            ['nombre' => 'usuarios'],
            ['nombre' => 'roles'],
            ['nombre' => 'farmacias'],
            ['nombre' => 'pedidos'],
            ['nombre' => 'repartidores'],
            ['nombre' => 'vehiculos'],
            ['nombre' => 'rutas'],
            ['nombre' => 'control_rutas'],
            ['nombre' => 'despachos'],
        ]);

        EstadoPedido::insert([
            ['nombre_estado' => 'Pendiente'],
            ['nombre_estado' => 'Aprobado'],
            ['nombre_estado' => 'En preparacion'],
            ['nombre_estado' => 'Despachado'],
            ['nombre_estado' => 'Entregado'],
            ['nombre_estado' => 'Cancelado'],
        ]);

        EstadoRepartidor::insert([
            ['nombre_estado' => 'Disponible'],
            ['nombre_estado' => 'En ruta'],
            ['nombre_estado' => 'Inactivo'],
        ]);

        EstadoVehiculo::insert([
            ['nombre_estado' => 'Operativo'],
            ['nombre_estado' => 'En mantenimiento'],
            ['nombre_estado' => 'Fuera de servicio'],
        ]);

        EstadoDespacho::insert([
            ['nombre_estado' => 'Pendiente'],
            ['nombre_estado' => 'En camino'],
            ['nombre_estado' => 'Entregado'],
            ['nombre_estado' => 'Fallido'],
        ]);

        Licencia::insert([
            ['categoria' => 'A'],
            ['categoria' => 'B'],
            ['categoria' => 'C'],
            ['categoria' => 'P'],
            ['categoria' => 'Profesional'],
        ]);

        ExtensionCI::insert([
            ['nombre_extension' => 'LP'],
            ['nombre_extension' => 'CBBA'],
            ['nombre_extension' => 'SC'],
            ['nombre_extension' => 'OR'],
            ['nombre_extension' => 'PT'],
            ['nombre_extension' => 'TJ'],
            ['nombre_extension' => 'CH'],
            ['nombre_extension' => 'BE'],
            ['nombre_extension' => 'PD'],
        ]);

        Marca::insert([
            ['nombre_marca' => 'Toyota'],
            ['nombre_marca' => 'Suzuki'],
            ['nombre_marca' => 'Nissan'],
            ['nombre_marca' => 'Mitsubishi'],
            ['nombre_marca' => 'Kia'],
        ]);

        Modelo::insert([
            ['id_marca' => 1, 'nombre_modelo' => 'Hilux'],
            ['id_marca' => 1, 'nombre_modelo' => 'Land Cruiser'],
            ['id_marca' => 2, 'nombre_modelo' => 'Vitara'],
            ['id_marca' => 2, 'nombre_modelo' => 'Carry'],
            ['id_marca' => 3, 'nombre_modelo' => 'Frontier'],
            ['id_marca' => 3, 'nombre_modelo' => 'NP300'],
            ['id_marca' => 4, 'nombre_modelo' => 'L200'],
            ['id_marca' => 4, 'nombre_modelo' => 'Montero Sport'],
            ['id_marca' => 5, 'nombre_modelo' => 'K2500'],
            ['id_marca' => 5, 'nombre_modelo' => 'Bongo'],
        ]);

        Capacidad::insert([
            ['capacidad_kg' => 500],
            ['capacidad_kg' => 1000],
            ['capacidad_kg' => 2000],
            ['capacidad_kg' => 3000],
            ['capacidad_kg' => 5000],
        ]);

        TipoIncidencia::insert([
            ['nombre_tipo' => 'Retraso en la entrega'],
            ['nombre_tipo' => 'Producto dañado'],
            ['nombre_tipo' => 'Direccion incorrecta'],
            ['nombre_tipo' => 'Cliente ausente'],
            ['nombre_tipo' => 'Fallo mecanico'],
            ['nombre_tipo' => 'Otro'],
        ]);

        TipoEvidencia::insert([
            ['nombre_tipo' => 'Foto de entrega'],
            ['nombre_tipo' => 'Firma digital'],
            ['nombre_tipo' => 'Documento adjunto'],
        ]);

        Cargo::insert([
            ['nombre_cargo' => 'Gerente'],
            ['nombre_cargo' => 'Administrador'],
            ['nombre_cargo' => 'Farmaceutico'],
            ['nombre_cargo' => 'Recepcionista'],
            ['nombre_cargo' => 'Almacenero'],
        ]);
    }
}
