<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    Route::get('usuarios/{usuario}/roles', [UsuarioController::class, 'roles']);
    Route::post('usuarios/{usuario}/roles', [UsuarioController::class, 'assignRoles']);
    Route::apiResource('usuarios', UsuarioController::class);

    Route::get('roles/{rol}/permisos', [RolController::class, 'permisos']);
    Route::post('roles/{rol}/permisos', [RolController::class, 'assignPermisos']);
    Route::apiResource('roles', RolController::class);

    Route::apiResource('farmacias', FarmaciaController::class);
    Route::apiResource('farmacias.contactos', ContactoFarmaciaController::class);

    Route::post('pedidos/{pedido}/cambiar-estado', [PedidoController::class, 'cambiarEstado']);
    Route::apiResource('pedidos', PedidoController::class);

    Route::post('repartidores/{repartidor}/cambiar-estado', [RepartidorController::class, 'cambiarEstado']);
    Route::apiResource('repartidores', RepartidorController::class);

    Route::post('vehiculos/{vehiculo}/cambiar-estado', [VehiculoController::class, 'cambiarEstado']);
    Route::apiResource('vehiculos', VehiculoController::class);

    Route::get('rutas/{ruta}/paradas', [RutaController::class, 'paradas']);
    Route::post('rutas/{ruta}/paradas', [RutaController::class, 'storeParada']);
    Route::put('rutas/{ruta}/paradas/{parada}', [RutaController::class, 'updateParada']);
    Route::delete('rutas/{ruta}/paradas/{parada}', [RutaController::class, 'destroyParada']);
    Route::apiResource('rutas', RutaController::class);

    Route::post('controles-ruta/{control_ruta}/registrar-llegada', [ControlRutaController::class, 'registrarLlegada']);
    Route::apiResource('controles-ruta', ControlRutaController::class);

    Route::post('despachos/{despacho}/cambiar-estado', [DespachoController::class, 'cambiarEstado']);
    Route::apiResource('despachos', DespachoController::class);
    Route::apiResource('despachos.incidencias', IncidenciaController::class);
    Route::apiResource('despachos.evidencias', EvidenciaController::class);

    Route::prefix('reportes')->group(function () {
        Route::get('resumen', [ReporteController::class, 'resumen']);
        Route::get('pedidos-por-estado', [ReporteController::class, 'pedidosPorEstado']);
        Route::get('despachos-por-estado', [ReporteController::class, 'despachosPorEstado']);
        Route::get('pedidos-por-dia', [ReporteController::class, 'pedidosPorDia']);
        Route::get('repartidores-por-estado', [ReporteController::class, 'repartidoresPorEstado']);
        Route::get('vehiculos-por-estado', [ReporteController::class, 'vehiculosPorEstado']);
        Route::get('incidencias-por-tipo', [ReporteController::class, 'incidenciasPorTipo']);
    });
});
