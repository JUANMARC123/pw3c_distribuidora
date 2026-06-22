<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\Seguridad\UsuarioController;
use App\Http\Controllers\Seguridad\RolController;
use App\Http\Controllers\Farmacia\FarmaciaController;
use App\Http\Controllers\Farmacia\ContactoFarmaciaController;
use App\Http\Controllers\Pedido\PedidoController;
use App\Http\Controllers\Repartidor\RepartidorController;
use App\Http\Controllers\Vehiculo\VehiculoController;
use App\Http\Controllers\Logistica\RutaController;
use App\Http\Controllers\Logistica\ControlRutaController;
use App\Http\Controllers\Despacho\DespachoController;
use App\Http\Controllers\Despacho\IncidenciaController;
use App\Http\Controllers\Despacho\EvidenciaController;
use App\Http\Controllers\Reportes\ReporteController;
use App\Http\Controllers\Medicamento\ProductoController;
use App\Http\Controllers\Inventario\InventarioController;
use App\Http\Controllers\Compra\ProveedorController;
use App\Http\Controllers\Compra\OrdenCompraController;
use App\Http\Controllers\Devolucion\DevolucionController;
use App\Http\Controllers\Promocion\PromocionController;
use App\Http\Controllers\Venta\VentaController;
use App\Http\Controllers\Inventario\AlmacenController;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // Módulo: Usuarios
    Route::get('usuarios/{usuario}/roles', [UsuarioController::class, 'roles'])->middleware('permission:Usuarios,asignar-roles');
    Route::post('usuarios/{usuario}/roles', [UsuarioController::class, 'assignRoles'])->middleware('permission:Usuarios,asignar-roles');
    Route::apiResource('usuarios', UsuarioController::class)->middleware('permission:Usuarios');
    Route::put('/usuarios/{id}/bloquear', [UsuarioController::class, 'bloquear'])->middleware('permission:Usuarios,bloquear');

    // Módulo: Roles
    Route::get('roles/{rol}/permisos', [RolController::class, 'permisos'])->middleware('permission:Roles,asignar-permisos');
    Route::post('roles/{rol}/permisos', [RolController::class, 'assignPermisos'])->middleware('permission:Roles,asignar-permisos');
    Route::apiResource('roles', RolController::class)->middleware('permission:Roles');

    // Módulo: Farmacias
    Route::apiResource('farmacias', FarmaciaController::class)->middleware('permission:Farmacias');
    Route::get('farmacias/{farmacia}/contactos', [ContactoFarmaciaController::class, 'index'])->middleware('permission:Farmacias,listar');
    Route::get('farmacias/{farmacia}/contactos/{contacto}', [ContactoFarmaciaController::class, 'show'])->middleware('permission:Farmacias,listar');
    Route::post('farmacias/{farmacia}/contactos', [ContactoFarmaciaController::class, 'store'])->middleware('permission:Farmacias,gestionar-contactos');
    Route::put('farmacias/{farmacia}/contactos/{contacto}', [ContactoFarmaciaController::class, 'update'])->middleware('permission:Farmacias,gestionar-contactos');
    Route::delete('farmacias/{farmacia}/contactos/{contacto}', [ContactoFarmaciaController::class, 'destroy'])->middleware('permission:Farmacias,gestionar-contactos');

    // Módulo: Pedidos
    Route::post('pedidos/{pedido}/cambiar-estado', [PedidoController::class, 'cambiarEstado'])->middleware('permission:Pedidos,cambiar-estado');
    Route::apiResource('pedidos', PedidoController::class)->middleware('permission:Pedidos');

    // Módulo: Repartidores
    Route::post('repartidores/{repartidor}/cambiar-estado', [RepartidorController::class, 'cambiarEstado'])->middleware('permission:Repartidores,cambiar-estado');
    Route::apiResource('repartidores', RepartidorController::class)->middleware('permission:Repartidores');

    // Módulo: Vehículos
    Route::post('vehiculos/{vehiculo}/cambiar-estado', [VehiculoController::class, 'cambiarEstado'])->middleware('permission:Vehículos,cambiar-estado');
    Route::apiResource('vehiculos', VehiculoController::class)->middleware('permission:Vehículos');

    // Módulo: Rutas
    Route::get('rutas/{ruta}/paradas', [RutaController::class, 'paradas'])->middleware('permission:Rutas,gestionar-paradas');
    Route::post('rutas/{ruta}/paradas', [RutaController::class, 'storeParada'])->middleware('permission:Rutas,gestionar-paradas');
    Route::get('rutas/{ruta}/paradas/{parada}', [RutaController::class, 'showParada'])->middleware('permission:Rutas,gestionar-paradas');
    Route::put('rutas/{ruta}/paradas/{parada}', [RutaController::class, 'updateParada'])->middleware('permission:Rutas,gestionar-paradas');
    Route::delete('rutas/{ruta}/paradas/{parada}', [RutaController::class, 'destroyParada'])->middleware('permission:Rutas,gestionar-paradas');
    Route::apiResource('rutas', RutaController::class)->middleware('permission:Rutas');

    // Módulo: Control Rutas
    Route::post('controles-ruta/{control_ruta}/registrar-llegada', [ControlRutaController::class, 'registrarLlegada'])->middleware('permission:Control Rutas,registrar-llegada');
    Route::apiResource('controles-ruta', ControlRutaController::class)->middleware('permission:Control Rutas');

    // Módulo: Despachos
    Route::post('despachos/{despacho}/cambiar-estado', [DespachoController::class, 'cambiarEstado'])->middleware('permission:Despachos,cambiar-estado');
    Route::apiResource('despachos', DespachoController::class)->middleware('permission:Despachos');
    Route::get('despachos/{despacho}/incidencias', [IncidenciaController::class, 'index'])->middleware('permission:Despachos,listar');
    Route::get('despachos/{despacho}/incidencias/{incidencia}', [IncidenciaController::class, 'show'])->middleware('permission:Despachos,listar');
    Route::post('despachos/{despacho}/incidencias', [IncidenciaController::class, 'store'])->middleware('permission:Despachos,gestionar-incidencias');
    Route::put('despachos/{despacho}/incidencias/{incidencia}', [IncidenciaController::class, 'update'])->middleware('permission:Despachos,gestionar-incidencias');
    Route::delete('despachos/{despacho}/incidencias/{incidencia}', [IncidenciaController::class, 'destroy'])->middleware('permission:Despachos,gestionar-incidencias');
    Route::get('despachos/{despacho}/evidencias', [EvidenciaController::class, 'index'])->middleware('permission:Despachos,listar');
    Route::get('despachos/{despacho}/evidencias/{evidencia}', [EvidenciaController::class, 'show'])->middleware('permission:Despachos,listar');
    Route::post('despachos/{despacho}/evidencias', [EvidenciaController::class, 'store'])->middleware('permission:Despachos,gestionar-evidencias');
    Route::put('despachos/{despacho}/evidencias/{evidencia}', [EvidenciaController::class, 'update'])->middleware('permission:Despachos,gestionar-evidencias');
    Route::delete('despachos/{despacho}/evidencias/{evidencia}', [EvidenciaController::class, 'destroy'])->middleware('permission:Despachos,gestionar-evidencias');

    // Módulo: Ventas
    Route::get('ventas/{venta}/pagos', [VentaController::class, 'pagos'])->middleware('permission:Ventas,listar');
    Route::post('ventas/{venta}/pagos', [VentaController::class, 'storePago'])->middleware('permission:Ventas,registrar-pago');
    Route::delete('ventas/{venta}/pagos/{pago}', [VentaController::class, 'destroyPago'])->middleware('permission:Ventas,registrar-pago');
    Route::apiResource('ventas', VentaController::class)->middleware('permission:Ventas');

    // Módulo: Compras
    Route::post('ordenes-compra/{ordenCompra}/cambiar-estado', [OrdenCompraController::class, 'cambiarEstado'])->middleware('permission:Compras,cambiar-estado');
    Route::apiResource('ordenes-compra', OrdenCompraController::class)->middleware('permission:Compras');
    Route::get('proveedores/{proveedor}/contactos', [ProveedorController::class, 'contactos'])->middleware('permission:Compras,listar');
    Route::post('proveedores/{proveedor}/contactos', [ProveedorController::class, 'storeContacto'])->middleware('permission:Compras,gestionar-contactos');
    Route::put('proveedores/{proveedor}/contactos/{contacto}', [ProveedorController::class, 'updateContacto'])->middleware('permission:Compras,gestionar-contactos');
    Route::delete('proveedores/{proveedor}/contactos/{contacto}', [ProveedorController::class, 'destroyContacto'])->middleware('permission:Compras,gestionar-contactos');
    Route::apiResource('proveedores', ProveedorController::class)->middleware('permission:Compras');

    // Módulo: Devoluciones
    Route::post('devoluciones/{devolucion}/cambiar-estado', [DevolucionController::class, 'cambiarEstado'])->middleware('permission:Devoluciones,cambiar-estado');
    Route::apiResource('devoluciones', DevolucionController::class)->middleware('permission:Devoluciones');

    // Módulo: Promociones
    Route::apiResource('promociones', PromocionController::class)->middleware('permission:Promociones');

    // Módulo: Inventario
    Route::get('inventario/alertas', [InventarioController::class, 'alertas'])->middleware('permission:Inventario,listar');
    Route::post('inventario/movimientos', [InventarioController::class, 'storeMovimiento'])->middleware('permission:Inventario,registrar-movimiento');
    Route::put('inventario/{inventario}', [InventarioController::class, 'update'])->middleware('permission:Inventario,editar');
    Route::get('inventario/{inventario}/movimientos', [InventarioController::class, 'movimientos'])->middleware('permission:Inventario,listar');
    Route::apiResource('inventario', InventarioController::class)->middleware('permission:Inventario')->only(['index', 'show']);

    // Módulo: Almacenes
    Route::apiResource('almacenes', AlmacenController::class)->middleware('permission:Almacenes');
    Route::get('almacenes/{almacen}/ubicaciones', [AlmacenController::class, 'ubicaciones'])->middleware('permission:Almacenes,gestionar-ubicaciones');
    Route::post('almacenes/{almacen}/ubicaciones', [AlmacenController::class, 'storeUbicacion'])->middleware('permission:Almacenes,gestionar-ubicaciones');
    Route::put('almacenes/{almacen}/ubicaciones/{ubicacion}', [AlmacenController::class, 'updateUbicacion'])->middleware('permission:Almacenes,gestionar-ubicaciones');
    Route::delete('almacenes/{almacen}/ubicaciones/{ubicacion}', [AlmacenController::class, 'destroyUbicacion'])->middleware('permission:Almacenes,gestionar-ubicaciones');

    // Módulo: Medicamentos
    Route::get('productos/{producto}/lotes', [ProductoController::class, 'lotes'])->middleware('permission:Productos,listar');
    Route::post('productos/{producto}/lotes', [ProductoController::class, 'storeLote'])->middleware('permission:Productos,gestionar-lotes');
    Route::put('productos/{producto}/lotes/{lote}', [ProductoController::class, 'updateLote'])->middleware('permission:Productos,gestionar-lotes');
    Route::delete('productos/{producto}/lotes/{lote}', [ProductoController::class, 'destroyLote'])->middleware('permission:Productos,gestionar-lotes');
    Route::apiResource('productos', ProductoController::class)->middleware('permission:Productos');

    // Módulo: Reportes
    Route::prefix('reportes')->group(function () {
        Route::get('resumen', [ReporteController::class, 'resumen'])->middleware('permission:Reportes,acceder');
        Route::get('pedidos-por-estado', [ReporteController::class, 'pedidosPorEstado'])->middleware('permission:Reportes,listar');
        Route::get('despachos-por-estado', [ReporteController::class, 'despachosPorEstado'])->middleware('permission:Reportes,listar');
        Route::get('pedidos-por-dia', [ReporteController::class, 'pedidosPorDia'])->middleware('permission:Reportes,listar');
        Route::get('repartidores-por-estado', [ReporteController::class, 'repartidoresPorEstado'])->middleware('permission:Reportes,listar');
        Route::get('vehiculos-por-estado', [ReporteController::class, 'vehiculosPorEstado'])->middleware('permission:Reportes,listar');
        Route::get('incidencias-por-tipo', [ReporteController::class, 'incidenciasPorTipo'])->middleware('permission:Reportes,listar');
    });

    // Catálogos (lectura: cualquier autenticado; escritura: requiere permisos del módulo Usuarios vía auto-detección)
    Route::prefix('catalogos')->group(function () {
        Route::get('modelos-por-marca/{idMarca}', [CatalogoController::class, 'modelosPorMarca']);
        Route::get('estados-usuario', [CatalogoController::class, 'estadosUsuario']);
        Route::get('roles', [CatalogoController::class, 'roles']);
        Route::get('modulos', [CatalogoController::class, 'modulos']);
        Route::get('acciones', [CatalogoController::class, 'acciones']);
        Route::get('tablas-sistema', [CatalogoController::class, 'tablasSistema']);
        Route::get('estados-pedido', [CatalogoController::class, 'estadosPedido']);
        Route::get('estados-repartidor', [CatalogoController::class, 'estadosRepartidor']);
        Route::get('estados-vehiculo', [CatalogoController::class, 'estadosVehiculo']);
        Route::get('estados-despacho', [CatalogoController::class, 'estadosDespacho']);
        Route::get('extensiones-ci', [CatalogoController::class, 'extensionesCi']);
        Route::get('licencias', [CatalogoController::class, 'licencias']);
        Route::get('marcas', [CatalogoController::class, 'marcas']);
        Route::get('modelos', [CatalogoController::class, 'modelos']);
        Route::get('capacidades', [CatalogoController::class, 'capacidades']);
        Route::get('tipos-incidencia', [CatalogoController::class, 'tiposIncidencia']);
        Route::get('tipos-evidencia', [CatalogoController::class, 'tiposEvidencia']);
        Route::get('cargos', [CatalogoController::class, 'cargos']);
        Route::get('estados-farmacia', [CatalogoController::class, 'estadosFarmacia']);
        Route::get('categorias', [CatalogoController::class, 'categorias']);
        Route::get('laboratorios', [CatalogoController::class, 'laboratorios']);
        Route::get('presentaciones', [CatalogoController::class, 'presentaciones']);
        Route::get('unidades-medida', [CatalogoController::class, 'unidadesMedida']);
        Route::get('tipos-movimiento', [CatalogoController::class, 'tiposMovimiento']);
        Route::get('estados-orden-compra', [CatalogoController::class, 'estadosOrdenCompra']);
        Route::get('tipos-devolucion', [CatalogoController::class, 'tiposDevolucion']);
        Route::get('estados-devolucion', [CatalogoController::class, 'estadosDevolucion']);
        Route::get('tipos-promocion', [CatalogoController::class, 'tiposPromocion']);
        Route::get('estados-venta', [CatalogoController::class, 'estadosVenta']);
        Route::get('metodos-pago', [CatalogoController::class, 'metodosPago']);

        Route::middleware('permission:Usuarios')->group(function () {
            Route::post('{catalogo}', [CatalogoController::class, 'store']);
            Route::put('{catalogo}/{id}', [CatalogoController::class, 'update'])->whereNumber('id');
            Route::delete('{catalogo}/{id}', [CatalogoController::class, 'destroy'])->whereNumber('id');
        });
    });
});
