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
use App\Models\Farmacia\EstadoFarmacia;
use App\Models\Medicamento\Categoria;
use App\Models\Medicamento\Laboratorio;
use App\Models\Medicamento\Presentacion;
use App\Models\Medicamento\UnidadMedida;
use App\Models\Inventario\TipoMovimiento;
use App\Models\Compra\EstadoOrdenCompra;
use App\Models\Devolucion\TipoDevolucion;
use App\Models\Devolucion\EstadoDevolucion;
use App\Models\Promocion\TipoPromocion;
use App\Models\Venta\EstadoVenta;
use App\Models\Venta\MetodoPago;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoSeeder extends Seeder
{
    private array $moduleActions = [
        'Dashboard'       => ['acceder'],
        'Reportes'        => ['acceder', 'listar'],
        'Usuarios'        => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'asignar-roles', 'bloquear'],
        'Roles'           => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'asignar-permisos'],
        'Farmacias'       => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'gestionar-contactos'],
        'Pedidos'         => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'cambiar-estado'],
        'Repartidores'    => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'cambiar-estado'],
        'Vehículos'       => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'cambiar-estado'],
        'Rutas'           => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'gestionar-paradas'],
        'Control Rutas'   => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'registrar-llegada'],
        'Despachos'       => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'cambiar-estado', 'gestionar-incidencias', 'gestionar-evidencias'],
        'Productos'       => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'gestionar-lotes'],
        'Inventario'      => ['acceder', 'listar', 'editar', 'registrar-movimiento'],
        'Compras'         => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'cambiar-estado', 'gestionar-contactos'],
        'Ventas'          => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'cambiar-estado', 'registrar-pago'],
        'Devoluciones'    => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'cambiar-estado'],
        'Promociones'     => ['acceder', 'listar', 'crear', 'editar', 'eliminar'],
        'Almacenes'       => ['acceder', 'listar', 'crear', 'editar', 'eliminar', 'gestionar-ubicaciones'],
    ];

    public function run()
    {
        EstadoUsuario::insertOrIgnore([
            ['nombre_estado' => 'Activo'],
            ['nombre_estado' => 'Bloqueado'],
            ['nombre_estado' => 'Suspendido'],
        ]);

        Rol::insertOrIgnore([
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
                ['Ventas', 'acceder'], ['Ventas', 'listar'], ['Ventas', 'crear'], ['Ventas', 'editar'], ['Ventas', 'cambiar-estado'], ['Ventas', 'registrar-pago'],
                ['Repartidores', 'acceder'], ['Repartidores', 'listar'], ['Repartidores', 'crear'], ['Repartidores', 'editar'], ['Repartidores', 'cambiar-estado'],
                ['Vehículos', 'acceder'], ['Vehículos', 'listar'], ['Vehículos', 'crear'], ['Vehículos', 'editar'], ['Vehículos', 'cambiar-estado'],
                ['Rutas', 'acceder'], ['Rutas', 'listar'], ['Rutas', 'crear'], ['Rutas', 'editar'], ['Rutas', 'gestionar-paradas'],
                ['Control Rutas', 'acceder'], ['Control Rutas', 'listar'], ['Control Rutas', 'crear'], ['Control Rutas', 'editar'], ['Control Rutas', 'registrar-llegada'],
                ['Despachos', 'acceder'], ['Despachos', 'listar'], ['Despachos', 'crear'], ['Despachos', 'editar'], ['Despachos', 'cambiar-estado'], ['Despachos', 'gestionar-incidencias'], ['Despachos', 'gestionar-evidencias'],
                ['Productos', 'acceder'], ['Productos', 'listar'], ['Productos', 'crear'], ['Productos', 'editar'], ['Productos', 'gestionar-lotes'],
                ['Inventario', 'acceder'], ['Inventario', 'listar'], ['Inventario', 'editar'], ['Inventario', 'registrar-movimiento'],
                ['Compras', 'acceder'], ['Compras', 'listar'], ['Compras', 'crear'], ['Compras', 'editar'], ['Compras', 'cambiar-estado'], ['Compras', 'gestionar-contactos'],
                ['Devoluciones', 'acceder'], ['Devoluciones', 'listar'], ['Devoluciones', 'crear'], ['Devoluciones', 'editar'], ['Devoluciones', 'cambiar-estado'],
                ['Promociones', 'acceder'], ['Promociones', 'listar'], ['Promociones', 'crear'], ['Promociones', 'editar'],
                ['Almacenes', 'acceder'], ['Almacenes', 'listar'], ['Almacenes', 'crear'], ['Almacenes', 'editar'], ['Almacenes', 'gestionar-ubicaciones'],
            ],
            3 => [
                ['Dashboard', 'acceder'],
                ['Reportes', 'acceder'], ['Reportes', 'listar'],
                ['Farmacias', 'acceder'], ['Farmacias', 'listar'],
                ['Pedidos', 'acceder'], ['Pedidos', 'listar'], ['Pedidos', 'crear'], ['Pedidos', 'editar'], ['Pedidos', 'cambiar-estado'],
                ['Ventas', 'acceder'], ['Ventas', 'listar'], ['Ventas', 'crear'], ['Ventas', 'editar'], ['Ventas', 'cambiar-estado'],
                ['Repartidores', 'acceder'], ['Repartidores', 'listar'],
                ['Vehículos', 'acceder'], ['Vehículos', 'listar'],
                ['Rutas', 'acceder'], ['Rutas', 'listar'],
                ['Control Rutas', 'acceder'], ['Control Rutas', 'listar'], ['Control Rutas', 'crear'], ['Control Rutas', 'editar'],
                ['Despachos', 'acceder'], ['Despachos', 'listar'], ['Despachos', 'crear'], ['Despachos', 'editar'], ['Despachos', 'cambiar-estado'],
                ['Productos', 'acceder'], ['Productos', 'listar'],
                ['Inventario', 'acceder'], ['Inventario', 'listar'],
                ['Compras', 'acceder'], ['Compras', 'listar'],
                ['Devoluciones', 'acceder'], ['Devoluciones', 'listar'],
                ['Promociones', 'acceder'], ['Promociones', 'listar'],
                ['Almacenes', 'acceder'], ['Almacenes', 'listar'],
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
            ['nombre' => 'productos'],
            ['nombre' => 'inventario'],
            ['nombre' => 'compras'],
            ['nombre' => 'ventas'],
            ['nombre' => 'devoluciones'],
            ['nombre' => 'promociones'],
            ['nombre' => 'ventas'],
            ['nombre' => 'metodos_pago'],
            ['nombre' => 'lotes'],
            ['nombre' => 'movimientos_inventario'],
            ['nombre' => 'proveedores'],
            ['nombre' => 'ordenes_compra'],
            ['nombre' => 'almacenes'],
            ['nombre' => 'ubicaciones_almacen'],
        ]);

        EstadoPedido::insertOrIgnore([
            ['nombre_estado' => 'Pendiente'],
            ['nombre_estado' => 'Aprobado'],
            ['nombre_estado' => 'En preparacion'],
            ['nombre_estado' => 'Despachado'],
            ['nombre_estado' => 'Entregado'],
            ['nombre_estado' => 'Cancelado'],
        ]);

        EstadoRepartidor::insertOrIgnore([
            ['nombre_estado' => 'Disponible'],
            ['nombre_estado' => 'En ruta'],
            ['nombre_estado' => 'Inactivo'],
        ]);

        EstadoVehiculo::insertOrIgnore([
            ['nombre_estado' => 'Operativo'],
            ['nombre_estado' => 'En mantenimiento'],
            ['nombre_estado' => 'Fuera de servicio'],
        ]);

        EstadoDespacho::insertOrIgnore([
            ['nombre_estado' => 'Pendiente'],
            ['nombre_estado' => 'En camino'],
            ['nombre_estado' => 'Entregado'],
            ['nombre_estado' => 'Fallido'],
        ]);

        Licencia::insertOrIgnore([
            ['categoria' => 'A'],
            ['categoria' => 'B'],
            ['categoria' => 'C'],
            ['categoria' => 'P'],
            ['categoria' => 'Profesional'],
        ]);

        ExtensionCI::insertOrIgnore([
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

        Marca::insertOrIgnore([
            ['nombre_marca' => 'Toyota'],
            ['nombre_marca' => 'Suzuki'],
            ['nombre_marca' => 'Nissan'],
            ['nombre_marca' => 'Mitsubishi'],
            ['nombre_marca' => 'Kia'],
        ]);

        Modelo::insertOrIgnore([
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

        Capacidad::insertOrIgnore([
            ['capacidad_kg' => 500],
            ['capacidad_kg' => 1000],
            ['capacidad_kg' => 2000],
            ['capacidad_kg' => 3000],
            ['capacidad_kg' => 5000],
        ]);

        TipoIncidencia::insertOrIgnore([
            ['nombre_tipo' => 'Retraso en la entrega'],
            ['nombre_tipo' => 'Producto dañado'],
            ['nombre_tipo' => 'Direccion incorrecta'],
            ['nombre_tipo' => 'Cliente ausente'],
            ['nombre_tipo' => 'Fallo mecanico'],
            ['nombre_tipo' => 'Otro'],
        ]);

        TipoEvidencia::insertOrIgnore([
            ['nombre_tipo' => 'Foto de entrega'],
            ['nombre_tipo' => 'Firma digital'],
            ['nombre_tipo' => 'Documento adjunto'],
        ]);

        Cargo::insertOrIgnore([
            ['nombre_cargo' => 'Gerente'],
            ['nombre_cargo' => 'Administrador'],
            ['nombre_cargo' => 'Farmaceutico'],
            ['nombre_cargo' => 'Recepcionista'],
            ['nombre_cargo' => 'Almacenero'],
        ]);

        EstadoFarmacia::insertOrIgnore([
            ['nombre_estado' => 'Activa'],
            ['nombre_estado' => 'Inactiva'],
            ['nombre_estado' => 'Suspendida'],
        ]);

        Categoria::insertOrIgnore([
            ['nombre_categoria' => 'Analgésicos'],
            ['nombre_categoria' => 'Antibióticos'],
            ['nombre_categoria' => 'Antiinflamatorios'],
            ['nombre_categoria' => 'Antihistamínicos'],
            ['nombre_categoria' => 'Cardiovasculares'],
            ['nombre_categoria' => 'Gastrointestinales'],
            ['nombre_categoria' => 'Vitaminas y Suplementos'],
        ]);

        Laboratorio::insertOrIgnore([
            ['nombre_laboratorio' => 'Bago', 'telefono' => null, 'direccion' => null],
            ['nombre_laboratorio' => 'Roche', 'telefono' => null, 'direccion' => null],
            ['nombre_laboratorio' => 'Pfizer', 'telefono' => null, 'direccion' => null],
            ['nombre_laboratorio' => 'Bayer', 'telefono' => null, 'direccion' => null],
            ['nombre_laboratorio' => 'Novartis', 'telefono' => null, 'direccion' => null],
        ]);

        Presentacion::insertOrIgnore([
            ['nombre_presentacion' => 'Caja x 10'],
            ['nombre_presentacion' => 'Caja x 20'],
            ['nombre_presentacion' => 'Caja x 30'],
            ['nombre_presentacion' => 'Blister x 10'],
            ['nombre_presentacion' => 'Frasco x 60ml'],
            ['nombre_presentacion' => 'Frasco x 100ml'],
            ['nombre_presentacion' => 'Ampolla x 1'],
            ['nombre_presentacion' => 'Tubo x 30g'],
        ]);

        UnidadMedida::insertOrIgnore([
            ['nombre_unidad' => 'Tableta'],
            ['nombre_unidad' => 'Cápsula'],
            ['nombre_unidad' => 'ml'],
            ['nombre_unidad' => 'mg'],
            ['nombre_unidad' => 'g'],
            ['nombre_unidad' => 'Ampolla'],
        ]);

        TipoMovimiento::insertOrIgnore([
            ['nombre_tipo' => 'Entrada'],
            ['nombre_tipo' => 'Salida'],
            ['nombre_tipo' => 'Ajuste (+)'],
            ['nombre_tipo' => 'Ajuste (-)'],
            ['nombre_tipo' => 'Vencimiento'],
        ]);

        EstadoOrdenCompra::insertOrIgnore([
            ['nombre_estado' => 'Pendiente'],
            ['nombre_estado' => 'Aprobada'],
            ['nombre_estado' => 'Enviada'],
            ['nombre_estado' => 'Recibida'],
            ['nombre_estado' => 'Cancelada'],
        ]);

        TipoDevolucion::insertOrIgnore([
            ['nombre_tipo' => 'Producto dañado'],
            ['nombre_tipo' => 'Producto equivocado'],
            ['nombre_tipo' => 'Producto vencido'],
            ['nombre_tipo' => 'Cliente insatisfecho'],
            ['nombre_tipo' => 'Otro'],
        ]);

        EstadoDevolucion::insertOrIgnore([
            ['nombre_estado' => 'Pendiente'],
            ['nombre_estado' => 'Aprobada'],
            ['nombre_estado' => 'Rechazada'],
            ['nombre_estado' => 'Completada'],
        ]);

        TipoPromocion::insertOrIgnore([
            ['nombre_tipo' => 'Descuento porcentual'],
            ['nombre_tipo' => 'Descuento fijo'],
            ['nombre_tipo' => '2x1'],
            ['nombre_tipo' => 'Bonificación'],
            ['nombre_tipo' => 'Compra mínima'],
        ]);

        EstadoVenta::insertOrIgnore([
            ['nombre_estado' => 'Pendiente'],
            ['nombre_estado' => 'Pagada'],
            ['nombre_estado' => 'Completada'],
            ['nombre_estado' => 'Cancelada'],
        ]);

        MetodoPago::insertOrIgnore([
            ['nombre_metodo' => 'Efectivo'],
            ['nombre_metodo' => 'Tarjeta de crédito'],
            ['nombre_metodo' => 'Tarjeta de débito'],
            ['nombre_metodo' => 'Transferencia bancaria'],
            ['nombre_metodo' => 'QR'],
        ]);
    }
}
