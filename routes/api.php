<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Seguridad\UsuarioController;
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

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // Usuarios
    Route::put('/usuarios/{id}/bloquear', [UsuarioController::class, 'bloquear']);
    Route::apiResource('usuarios', UsuarioController::class);

    // Farmacias
    Route::apiResource('farmacias', FarmaciaController::class);
    Route::get('farmacias/{farmacia}/contactos', [ContactoFarmaciaController::class, 'index']);
    Route::get('farmacias/{farmacia}/contactos/{contacto}', [ContactoFarmaciaController::class, 'show']);
    Route::post('farmacias/{farmacia}/contactos', [ContactoFarmaciaController::class, 'store']);
    Route::put('farmacias/{farmacia}/contactos/{contacto}', [ContactoFarmaciaController::class, 'update']);
    Route::delete('farmacias/{farmacia}/contactos/{contacto}', [ContactoFarmaciaController::class, 'destroy']);

    // Productos
    Route::apiResource('productos', ProductoController::class);

    // Pedidos
    Route::post('pedidos/extraer-pdf', [PedidoController::class, 'extraerPdf']);
    Route::post('pedidos/{pedido}/cambiar-estado', [PedidoController::class, 'cambiarEstado']);
    Route::post('pedidos/{pedido}/subir-archivo', [PedidoController::class, 'subirArchivo']);
    Route::get('pedidos/{pedido}/descargar-archivo', [PedidoController::class, 'descargarArchivo']);
    Route::apiResource('pedidos', PedidoController::class);
    Route::get('pedidos/{pedido}/detalles', [DetallePedidoController::class, 'index']);
    Route::post('pedidos/{pedido}/detalles', [DetallePedidoController::class, 'store']);
    Route::put('pedidos/{pedido}/detalles/{detalle}', [DetallePedidoController::class, 'update']);
    Route::delete('pedidos/{pedido}/detalles/{detalle}', [DetallePedidoController::class, 'destroy']);
    Route::put('pedidos/{pedido}/detalles/{detalle}/entrega', [DetallePedidoController::class, 'actualizarEntrega']);

    // Repartidores
    Route::post('repartidores/{repartidor}/cambiar-estado', [RepartidorController::class, 'cambiarEstado']);
    Route::get('repartidores/{repartidor}/asignacion-vehiculo', [RepartidorVehiculoController::class, 'show']);
    Route::post('repartidores/{repartidor}/asignacion-vehiculo', [RepartidorVehiculoController::class, 'store']);
    Route::delete('repartidores/{repartidor}/asignacion-vehiculo', [RepartidorVehiculoController::class, 'destroy']);
    Route::apiResource('repartidores', RepartidorController::class);

    // Vehículos
    Route::post('vehiculos/{vehiculo}/cambiar-estado', [VehiculoController::class, 'cambiarEstado']);
    Route::apiResource('vehiculos', VehiculoController::class);

    // Hojas de Ruta (Logística)
    Route::prefix('logistica')->group(function () {
        Route::get('pedidos-pendientes', [HojaRutaController::class, 'pedidosPendientes']);
        Route::get('repartidores-disponibles', [HojaRutaController::class, 'repartidoresDisponibles']);
        Route::get('rutas-activas', [HojaRutaController::class, 'rutasActivas']);
    });
    Route::post('hojas-ruta/{id}/iniciar', [HojaRutaController::class, 'iniciarRuta']);
    Route::post('hojas-ruta/{id}/registrar-llegada', [HojaRutaController::class, 'registrarLlegada']);
    Route::post('hojas-ruta/{id}/entrega-completada', [HojaRutaController::class, 'marcarEntregaCompletada']);
    Route::post('hojas-ruta/{id}/entrega-fallida', [HojaRutaController::class, 'marcarEntregaFallida']);
    Route::post('hojas-ruta/{id}/devolucion', [HojaRutaController::class, 'marcarDevolucion']);
    Route::post('hojas-ruta/{id}/regreso-almacen', [HojaRutaController::class, 'regresoAlmacen']);
    Route::apiResource('hojas-ruta', HojaRutaController::class);

    // Despachos
    Route::post('despachos/{despacho}/cambiar-estado', [DespachoController::class, 'cambiarEstado']);
    Route::apiResource('despachos', DespachoController::class);
    Route::get('despachos/{despacho}/incidencias', [IncidenciaController::class, 'index']);
    Route::get('despachos/{despacho}/incidencias/{incidencia}', [IncidenciaController::class, 'show']);
    Route::post('despachos/{despacho}/incidencias', [IncidenciaController::class, 'store']);
    Route::put('despachos/{despacho}/incidencias/{incidencia}', [IncidenciaController::class, 'update']);
    Route::delete('despachos/{despacho}/incidencias/{incidencia}', [IncidenciaController::class, 'destroy']);
    Route::get('despachos/{despacho}/evidencias', [EvidenciaController::class, 'index']);
    Route::get('despachos/{despacho}/evidencias/{evidencia}', [EvidenciaController::class, 'show']);
    Route::post('despachos/{despacho}/evidencias', [EvidenciaController::class, 'store']);
    Route::put('despachos/{despacho}/evidencias/{evidencia}', [EvidenciaController::class, 'update']);
    Route::delete('despachos/{despacho}/evidencias/{evidencia}', [EvidenciaController::class, 'destroy']);

    // Reportes
    Route::prefix('reportes')->group(function () {
        Route::get('resumen', [ReporteController::class, 'resumen']);
        Route::get('pedidos-por-estado', [ReporteController::class, 'pedidosPorEstado']);
        Route::get('despachos-por-estado', [ReporteController::class, 'despachosPorEstado']);
        Route::get('pedidos-por-dia', [ReporteController::class, 'pedidosPorDia']);
        Route::get('repartidores-por-estado', [ReporteController::class, 'repartidoresPorEstado']);
        Route::get('vehiculos-por-estado', [ReporteController::class, 'vehiculosPorEstado']);
        Route::get('incidencias-por-tipo', [ReporteController::class, 'incidenciasPorTipo']);
    });

    // Caja Diaria
    Route::prefix('caja')->group(function () {
        Route::post('abrir', [CajaController::class, 'abrirCaja']);
        Route::post('ingreso', [CajaController::class, 'registrarIngreso']);
        Route::post('gasto', [CajaController::class, 'registrarGasto']);
        Route::put('cerrar/{id}', [CajaController::class, 'cerrarCaja']);
        Route::get('dia', [CajaController::class, 'cajaDelDia']);
        Route::get('resumen', [CajaController::class, 'resumenDiario']);
    });
});
