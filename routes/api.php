<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Seguridad\UsuarioController;
use App\Http\Controllers\Seguridad\RolesController;
use App\Http\Controllers\Farmacia\FarmaciaController;
use App\Http\Controllers\Farmacia\ContactoFarmaciaController;
use App\Http\Controllers\Pedido\PedidoController;
use App\Http\Controllers\Repartidor\RepartidorController;
use App\Http\Controllers\Repartidor\RepartidorVehiculoController;
use App\Http\Controllers\Vehiculo\VehiculoController;
use App\Http\Controllers\Logistica\HojaRutaController;
use App\Http\Controllers\Despacho\DespachoController;
use App\Http\Controllers\Despacho\IncidenciaController;
use App\Http\Controllers\Despacho\EvidenciaController;
use App\Http\Controllers\Producto\ProductoController;
use App\Http\Controllers\Pedido\DetallePedidoController;
use App\Http\Controllers\Caja\CajaController;
use App\Http\Controllers\Reportes\ReporteController;

// ─── Rutas públicas ─────────────────────────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// ─── Rutas autenticadas ─────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth (solo autenticación, sin permisos adicionales)
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // ═══════════════════════════════════════════════════════════════
    // USUARIOS
    // ═══════════════════════════════════════════════════════════════
    Route::put('/usuarios/{id}/bloquear', [UsuarioController::class, 'bloquear'])
        ->middleware('permission:Usuarios-editar');
    Route::get('/usuarios', [UsuarioController::class, 'index'])
        ->middleware('permission:Usuarios-listar');
    Route::post('/usuarios', [UsuarioController::class, 'store'])
        ->middleware('permission:Usuarios-crear');
    Route::get('/usuarios/{id}', [UsuarioController::class, 'show'])
        ->middleware('permission:Usuarios-listar');
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])
        ->middleware('permission:Usuarios-editar');
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy'])
        ->middleware('permission:Usuarios-eliminar');

    // ═══════════════════════════════════════════════════════════════
    // ROLES
    // ═══════════════════════════════════════════════════════════════
    Route::get('/roles', [RolesController::class, 'index'])
        ->middleware('permission:Roles-listar');
    Route::post('/roles', [RolesController::class, 'store'])
        ->middleware('permission:Roles-crear');
    Route::get('/roles/{id}', [RolesController::class, 'show'])
        ->middleware('permission:Roles-listar');
    Route::put('/roles/{id}', [RolesController::class, 'update'])
        ->middleware('permission:Roles-editar');
    Route::delete('/roles/{id}', [RolesController::class, 'destroy'])
        ->middleware('permission:Roles-eliminar');
    Route::get('/roles/{id}/permisos', [RolesController::class, 'permisos'])
        ->middleware('permission:Roles-asignar-permisos');
    Route::post('/roles/{id}/permisos', [RolesController::class, 'asignarPermisos'])
        ->middleware('permission:Roles-asignar-permisos');

    // ═══════════════════════════════════════════════════════════════
    // FARMACIAS
    // ═══════════════════════════════════════════════════════════════
    Route::get('/farmacias', [FarmaciaController::class, 'index'])
        ->middleware('permission:Farmacias-listar');
    Route::post('/farmacias', [FarmaciaController::class, 'store'])
        ->middleware('permission:Farmacias-crear');
    Route::get('/farmacias/{farmacia}', [FarmaciaController::class, 'show'])
        ->middleware('permission:Farmacias-listar');
    Route::put('/farmacias/{farmacia}', [FarmaciaController::class, 'update'])
        ->middleware('permission:Farmacias-editar');
    Route::delete('/farmacias/{farmacia}', [FarmaciaController::class, 'destroy'])
        ->middleware('permission:Farmacias-eliminar');

    // Contactos de farmacia
    Route::get('/farmacias/{farmacia}/contactos', [ContactoFarmaciaController::class, 'index'])
        ->middleware('permission:Farmacias-gestionar-contactos');
    Route::post('/farmacias/{farmacia}/contactos', [ContactoFarmaciaController::class, 'store'])
        ->middleware('permission:Farmacias-gestionar-contactos');
    Route::get('/farmacias/{farmacia}/contactos/{contacto}', [ContactoFarmaciaController::class, 'show'])
        ->middleware('permission:Farmacias-gestionar-contactos');
    Route::put('/farmacias/{farmacia}/contactos/{contacto}', [ContactoFarmaciaController::class, 'update'])
        ->middleware('permission:Farmacias-gestionar-contactos');
    Route::delete('/farmacias/{farmacia}/contactos/{contacto}', [ContactoFarmaciaController::class, 'destroy'])
        ->middleware('permission:Farmacias-gestionar-contactos');

    // ═══════════════════════════════════════════════════════════════
    // PRODUCTOS
    // ═══════════════════════════════════════════════════════════════
    Route::get('/productos', [ProductoController::class, 'index'])
        ->middleware('permission:Productos-listar');
    Route::post('/productos', [ProductoController::class, 'store'])
        ->middleware('permission:Productos-crear');
    Route::get('/productos/{producto}', [ProductoController::class, 'show'])
        ->middleware('permission:Productos-listar');
    Route::put('/productos/{producto}', [ProductoController::class, 'update'])
        ->middleware('permission:Productos-editar');
    Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])
        ->middleware('permission:Productos-eliminar');

    // ═══════════════════════════════════════════════════════════════
    // PEDIDOS
    // ═══════════════════════════════════════════════════════════════
    // Rutas fijas (declaradas ANTES de {pedido} para evitar conflictos)
    Route::post('/pedidos/extraer-pdf', [PedidoController::class, 'extraerPdf'])
        ->middleware('permission:Pedidos-crear');
    Route::post('/pedidos/{pedido}/cambiar-estado', [PedidoController::class, 'cambiarEstado'])
        ->middleware('permission:Pedidos-cambiar-estado');
    Route::post('/pedidos/{pedido}/subir-archivo', [PedidoController::class, 'subirArchivo'])
        ->middleware('permission:Pedidos-editar');
    Route::get('/pedidos/{pedido}/descargar-archivo', [PedidoController::class, 'descargarArchivo'])
        ->middleware('permission:Pedidos-listar');

    // CRUD Pedidos
    Route::get('/pedidos', [PedidoController::class, 'index'])
        ->middleware('permission:Pedidos-listar');
    Route::post('/pedidos', [PedidoController::class, 'store'])
        ->middleware('permission:Pedidos-crear');
    Route::get('/pedidos/{pedido}', [PedidoController::class, 'show'])
        ->middleware('permission:Pedidos-listar');
    Route::put('/pedidos/{pedido}', [PedidoController::class, 'update'])
        ->middleware('permission:Pedidos-editar');
    Route::delete('/pedidos/{pedido}', [PedidoController::class, 'destroy'])
        ->middleware('permission:Pedidos-eliminar');

    // Detalles de pedido
    Route::get('/pedidos/{pedido}/detalles', [DetallePedidoController::class, 'index'])
        ->middleware('permission:Pedidos-listar');
    Route::post('/pedidos/{pedido}/detalles', [DetallePedidoController::class, 'store'])
        ->middleware('permission:Pedidos-editar');
    Route::put('/pedidos/{pedido}/detalles/{detalle}', [DetallePedidoController::class, 'update'])
        ->middleware('permission:Pedidos-editar');
    Route::delete('/pedidos/{pedido}/detalles/{detalle}', [DetallePedidoController::class, 'destroy'])
        ->middleware('permission:Pedidos-editar');
    Route::put('/pedidos/{pedido}/detalles/{detalle}/entrega', [DetallePedidoController::class, 'actualizarEntrega'])
        ->middleware('permission:Pedidos-cambiar-estado');

    // ═══════════════════════════════════════════════════════════════
    // REPARTIDORES
    // ═══════════════════════════════════════════════════════════════
    Route::post('/repartidores/{repartidor}/cambiar-estado', [RepartidorController::class, 'cambiarEstado'])
        ->middleware('permission:Repartidores-cambiar-estado');

    Route::get('/repartidores/{repartidor}/asignacion-vehiculo', [RepartidorVehiculoController::class, 'show'])
        ->middleware('permission:Repartidores-editar');
    Route::post('/repartidores/{repartidor}/asignacion-vehiculo', [RepartidorVehiculoController::class, 'store'])
        ->middleware('permission:Repartidores-editar');
    Route::delete('/repartidores/{repartidor}/asignacion-vehiculo', [RepartidorVehiculoController::class, 'destroy'])
        ->middleware('permission:Repartidores-editar');

    Route::get('/repartidores', [RepartidorController::class, 'index'])
        ->middleware('permission:Repartidores-listar');
    Route::post('/repartidores', [RepartidorController::class, 'store'])
        ->middleware('permission:Repartidores-crear');
    Route::get('/repartidores/{repartidor}', [RepartidorController::class, 'show'])
        ->middleware('permission:Repartidores-listar');
    Route::put('/repartidores/{repartidor}', [RepartidorController::class, 'update'])
        ->middleware('permission:Repartidores-editar');
    Route::delete('/repartidores/{repartidor}', [RepartidorController::class, 'destroy'])
        ->middleware('permission:Repartidores-eliminar');

    // ═══════════════════════════════════════════════════════════════
    // VEHÍCULOS
    // ═══════════════════════════════════════════════════════════════
    Route::post('/vehiculos/{vehiculo}/cambiar-estado', [VehiculoController::class, 'cambiarEstado'])
        ->middleware('permission:Vehículos-cambiar-estado');

    Route::get('/vehiculos', [VehiculoController::class, 'index'])
        ->middleware('permission:Vehículos-listar');
    Route::post('/vehiculos', [VehiculoController::class, 'store'])
        ->middleware('permission:Vehículos-crear');
    Route::get('/vehiculos/{vehiculo}', [VehiculoController::class, 'show'])
        ->middleware('permission:Vehículos-listar');
    Route::put('/vehiculos/{vehiculo}', [VehiculoController::class, 'update'])
        ->middleware('permission:Vehículos-editar');
    Route::delete('/vehiculos/{vehiculo}', [VehiculoController::class, 'destroy'])
        ->middleware('permission:Vehículos-eliminar');

    // ═══════════════════════════════════════════════════════════════
    // HOJAS DE RUTA (Logística)
    // ═══════════════════════════════════════════════════════════════
    // Rutas informativas con prefijo /logistica
    Route::prefix('logistica')->group(function () {
        Route::get('rutas-activas', [HojaRutaController::class, 'rutasActivas'])
            ->middleware('role_or_permission:Rutas-listar|Control Rutas-listar');
        Route::get('pedidos-pendientes', [HojaRutaController::class, 'pedidosPendientes'])
            ->middleware('permission:Rutas-listar');
        Route::get('repartidores-disponibles', [HojaRutaController::class, 'repartidoresDisponibles'])
            ->middleware('permission:Rutas-listar');
    });

    // Acciones sobre hojas de ruta (ejecutadas por repartidor en ruta)
    Route::post('/hojas-ruta/{id}/iniciar', [HojaRutaController::class, 'iniciarRuta'])
        ->middleware('permission:Rutas-editar');
    Route::post('/hojas-ruta/{id}/registrar-llegada', [HojaRutaController::class, 'registrarLlegada'])
        ->middleware('permission:Control Rutas-registrar-llegada');
    Route::post('/hojas-ruta/{id}/entrega-completada', [HojaRutaController::class, 'marcarEntregaCompletada'])
        ->middleware('permission:Despachos-cambiar-estado');
    Route::post('/hojas-ruta/{id}/entrega-fallida', [HojaRutaController::class, 'marcarEntregaFallida'])
        ->middleware('permission:Despachos-cambiar-estado');
    Route::post('/hojas-ruta/{id}/devolucion', [HojaRutaController::class, 'marcarDevolucion'])
        ->middleware('permission:Despachos-cambiar-estado');
    Route::post('/hojas-ruta/{id}/regreso-almacen', [HojaRutaController::class, 'regresoAlmacen'])
        ->middleware('permission:Rutas-editar');

    // CRUD Hojas de Ruta
    Route::get('/hojas-ruta', [HojaRutaController::class, 'index'])
        ->middleware('permission:Rutas-listar');
    Route::post('/hojas-ruta', [HojaRutaController::class, 'store'])
        ->middleware('permission:Rutas-crear');
    Route::get('/hojas-ruta/{hoja_ruta}', [HojaRutaController::class, 'show'])
        ->middleware('permission:Rutas-listar');
    Route::put('/hojas-ruta/{hoja_ruta}', [HojaRutaController::class, 'update'])
        ->middleware('permission:Rutas-editar');
    Route::delete('/hojas-ruta/{hoja_ruta}', [HojaRutaController::class, 'destroy'])
        ->middleware('permission:Rutas-eliminar');

    // ═══════════════════════════════════════════════════════════════
    // DESPACHOS
    // ═══════════════════════════════════════════════════════════════
    Route::post('/despachos/{despacho}/cambiar-estado', [DespachoController::class, 'cambiarEstado'])
        ->middleware('permission:Despachos-cambiar-estado');

    Route::get('/despachos', [DespachoController::class, 'index'])
        ->middleware('permission:Despachos-listar');
    Route::post('/despachos', [DespachoController::class, 'store'])
        ->middleware('permission:Despachos-crear');
    Route::get('/despachos/{despacho}', [DespachoController::class, 'show'])
        ->middleware('permission:Despachos-listar');
    Route::put('/despachos/{despacho}', [DespachoController::class, 'update'])
        ->middleware('permission:Despachos-editar');
    Route::delete('/despachos/{despacho}', [DespachoController::class, 'destroy'])
        ->middleware('permission:Despachos-eliminar');

    // Incidencias
    Route::get('/despachos/{despacho}/incidencias', [IncidenciaController::class, 'index'])
        ->middleware('permission:Despachos-gestionar-incidencias');
    Route::post('/despachos/{despacho}/incidencias', [IncidenciaController::class, 'store'])
        ->middleware('permission:Despachos-gestionar-incidencias');
    Route::get('/despachos/{despacho}/incidencias/{incidencia}', [IncidenciaController::class, 'show'])
        ->middleware('permission:Despachos-gestionar-incidencias');
    Route::put('/despachos/{despacho}/incidencias/{incidencia}', [IncidenciaController::class, 'update'])
        ->middleware('permission:Despachos-gestionar-incidencias');
    Route::delete('/despachos/{despacho}/incidencias/{incidencia}', [IncidenciaController::class, 'destroy'])
        ->middleware('permission:Despachos-gestionar-incidencias');

    // Evidencias
    Route::get('/despachos/{despacho}/evidencias', [EvidenciaController::class, 'index'])
        ->middleware('permission:Despachos-gestionar-evidencias');
    Route::post('/despachos/{despacho}/evidencias', [EvidenciaController::class, 'store'])
        ->middleware('permission:Despachos-gestionar-evidencias');
    Route::get('/despachos/{despacho}/evidencias/{evidencia}', [EvidenciaController::class, 'show'])
        ->middleware('permission:Despachos-gestionar-evidencias');
    Route::put('/despachos/{despacho}/evidencias/{evidencia}', [EvidenciaController::class, 'update'])
        ->middleware('permission:Despachos-gestionar-evidencias');
    Route::delete('/despachos/{despacho}/evidencias/{evidencia}', [EvidenciaController::class, 'destroy'])
        ->middleware('permission:Despachos-gestionar-evidencias');

    // ═══════════════════════════════════════════════════════════════
    // REPORTES
    // ═══════════════════════════════════════════════════════════════
    Route::prefix('reportes')->middleware('permission:Reportes-listar')->group(function () {
        Route::get('resumen', [ReporteController::class, 'resumen']);
        Route::get('pedidos-por-estado', [ReporteController::class, 'pedidosPorEstado']);
        Route::get('despachos-por-estado', [ReporteController::class, 'despachosPorEstado']);
        Route::get('pedidos-por-dia', [ReporteController::class, 'pedidosPorDia']);
        Route::get('repartidores-por-estado', [ReporteController::class, 'repartidoresPorEstado']);
        Route::get('vehiculos-por-estado', [ReporteController::class, 'vehiculosPorEstado']);
        Route::get('incidencias-por-tipo', [ReporteController::class, 'incidenciasPorTipo']);
    });

    // ═══════════════════════════════════════════════════════════════
    // CAJA DIARIA
    // ═══════════════════════════════════════════════════════════════
    Route::prefix('caja')->group(function () {
        Route::post('abrir', [CajaController::class, 'abrirCaja'])
            ->middleware('permission:Caja Diaria-crear');
        Route::post('ingreso', [CajaController::class, 'registrarIngreso'])
            ->middleware('permission:Caja Diaria-editar');
        Route::post('gasto', [CajaController::class, 'registrarGasto'])
            ->middleware('permission:Caja Diaria-editar');
        Route::put('cerrar/{id}', [CajaController::class, 'cerrarCaja'])
            ->middleware('permission:Caja Diaria-cerrar');
        Route::get('dia', [CajaController::class, 'cajaDelDia'])
            ->middleware('permission:Caja Diaria-listar');
        Route::get('resumen', [CajaController::class, 'resumenDiario'])
            ->middleware('permission:Caja Diaria-listar');
    });
});

// ─── Rutas mobile (app repartidor) ─────────────────────────────────
Route::prefix('mobile')->name('mobile.')->group(function () {
    Route::get('ruta-activa/{firebaseUid}', [\App\Http\Controllers\Mobile\RutaController::class, 'rutaActiva']);
    Route::post('registrar-llegada', [\App\Http\Controllers\Mobile\RutaController::class, 'registrarLlegada']);
    Route::get('pedido/{pedidoId}', [\App\Http\Controllers\Mobile\RutaController::class, 'detallePedido']);
    Route::put('detalle-entrega/{detalleId}', [\App\Http\Controllers\Mobile\RutaController::class, 'actualizarDetalleEntrega']);
    Route::post('evidencias/{despachoId}', [\App\Http\Controllers\Mobile\RutaController::class, 'storeEvidencia']);
    Route::post('incidencias/{despachoId}', [\App\Http\Controllers\Mobile\RutaController::class, 'storeIncidencia']);
    Route::post('regreso-almacen', [\App\Http\Controllers\Mobile\RutaController::class, 'regresoAlmacen']);
});
