<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Seguridad\UsuarioController;
use App\Http\Controllers\Seguridad\RolController;
use App\Http\Controllers\Farmacia\FarmaciaController;
use App\Http\Controllers\Farmacia\ContactoFarmaciaController;
use App\Http\Controllers\Pedido\PedidoController;
use App\Http\Controllers\Repartidor\RepartidorController;

Route::post('/auth/login', [AuthController::class, 'login']);

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
});
