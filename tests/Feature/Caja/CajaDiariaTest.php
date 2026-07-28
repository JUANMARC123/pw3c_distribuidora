<?php

/**
 * BDD: docs/specs/caja.md -> Escenarios J1, J2 (Ciclo de vida de caja, consultas)
 * @see \App\Http\Controllers\Caja\CajaController
 */

use App\Models\CajaRepartidor;
use App\Models\Repartidor;
use App\Models\Vehiculo;
use App\Models\AsignacionVehiculoRepartidor;

uses()->group('caja');

beforeEach(function () {
    $this->user = actingAsAdmin();
    $this->repartidor = Repartidor::factory()->create();
    $this->vehiculo = Vehiculo::factory()->create();
    AsignacionVehiculoRepartidor::factory()->create([
        'id_repartidor' => $this->repartidor->id_repartidor,
        'id_vehiculo' => $this->vehiculo->id_vehiculo,
    ]);
});

it('abre una caja', function () {
    // Preparar
    $farmacia = \App\Models\Farmacia::factory()->create();
    $pedidoResponse = $this->postJson('/api/pedidos', [
        'id_farmacia' => $farmacia->id_farmacia,
        'id_usuario' => $this->user->id_usuario,
        'estado' => 'aprobado',
        'total' => 150.00,
    ])->assertCreated();
    $pedido = $pedidoResponse->json('data');

    $hojaRutaResponse = $this->postJson('/api/hojas-ruta', [
        'fecha' => now()->format('Y-m-d'),
        'id_repartidor' => $this->repartidor->id_repartidor,
        'id_vehiculo' => $this->vehiculo->id_vehiculo,
        'pedidos' => [
            ['id_pedido' => $pedido['id_pedido'], 'orden_visita' => 1],
        ],
    ])->assertCreated();
    $hojaRuta = $hojaRutaResponse->json('data');

    // Actuar
    $response = $this->postJson('/api/caja/abrir', [
        'id_hoja_ruta' => $hojaRuta['id_hoja_ruta'],
        'id_repartidor' => $this->repartidor->id_repartidor,
        'fecha' => now()->format('Y-m-d'),
        'monto_inicial' => 200.00,
    ]);

    // Verificar
    $response->assertCreated();
});

it('registra un ingreso en caja abierta', function () {
    // Preparar
    $farmacia = \App\Models\Farmacia::factory()->create();
    $pedidoResponse = $this->postJson('/api/pedidos', [
        'id_farmacia' => $farmacia->id_farmacia,
        'id_usuario' => $this->user->id_usuario,
        'estado' => 'aprobado',
        'total' => 150.00,
    ])->assertCreated();
    $pedido = $pedidoResponse->json('data');

    $hojaRutaResponse = $this->postJson('/api/hojas-ruta', [
        'fecha' => now()->format('Y-m-d'),
        'id_repartidor' => $this->repartidor->id_repartidor,
        'id_vehiculo' => $this->vehiculo->id_vehiculo,
        'pedidos' => [
            ['id_pedido' => $pedido['id_pedido'], 'orden_visita' => 1],
        ],
    ])->assertCreated();
    $hojaRuta = $hojaRutaResponse->json('data');

    $cajaResponse = $this->postJson('/api/caja/abrir', [
        'id_hoja_ruta' => $hojaRuta['id_hoja_ruta'],
        'id_repartidor' => $this->repartidor->id_repartidor,
        'fecha' => now()->format('Y-m-d'),
        'monto_inicial' => 200.00,
    ])->assertCreated();
    $caja = $cajaResponse->json('data');

    // Actuar
    $response = $this->postJson('/api/caja/ingreso', [
        'id_caja' => $caja['id_caja'],
        'monto' => 350.00,
        'descripcion' => 'Cobro pedido #123',
    ]);

    // Verificar
    $response->assertOk();
});

it('registra un gasto en caja abierta', function () {
    // Preparar
    $farmacia = \App\Models\Farmacia::factory()->create();
    $pedidoResponse = $this->postJson('/api/pedidos', [
        'id_farmacia' => $farmacia->id_farmacia,
        'id_usuario' => $this->user->id_usuario,
        'estado' => 'aprobado',
        'total' => 150.00,
    ])->assertCreated();
    $pedido = $pedidoResponse->json('data');

    $hojaRutaResponse = $this->postJson('/api/hojas-ruta', [
        'fecha' => now()->format('Y-m-d'),
        'id_repartidor' => $this->repartidor->id_repartidor,
        'id_vehiculo' => $this->vehiculo->id_vehiculo,
        'pedidos' => [
            ['id_pedido' => $pedido['id_pedido'], 'orden_visita' => 1],
        ],
    ])->assertCreated();
    $hojaRuta = $hojaRutaResponse->json('data');

    $cajaResponse = $this->postJson('/api/caja/abrir', [
        'id_hoja_ruta' => $hojaRuta['id_hoja_ruta'],
        'id_repartidor' => $this->repartidor->id_repartidor,
        'fecha' => now()->format('Y-m-d'),
        'monto_inicial' => 200.00,
    ])->assertCreated();
    $caja = $cajaResponse->json('data');

    // Actuar
    $response = $this->postJson('/api/caja/gasto', [
        'id_caja' => $caja['id_caja'],
        'tipo_gasto' => 'COMBUSTIBLE',
        'monto' => 50.00,
        'descripcion' => 'Gasolina',
    ]);

    // Verificar
    $response->assertOk();
});

it('cierra una caja', function () {
    // Preparar
    $caja = CajaRepartidor::factory()->create([
        'id_repartidor' => $this->repartidor->id_repartidor,
    ]);

    // Actuar
    $response = $this->putJson("/api/caja/cerrar/{$caja->id_caja}", [
        'monto_final' => 500.00,
    ]);

    // Verificar
    $response->assertOk();
    expect(CajaRepartidor::find($caja->id_caja)->estado_caja)->toBe('cerrada');
});

it('consulta caja del dia', function () {
    // Preparar
    CajaRepartidor::factory()->create([
        'id_repartidor' => $this->repartidor->id_repartidor,
    ]);

    // Actuar
    $response = $this->getJson('/api/caja/dia');

    // Verificar
    $response->assertOk();
});

it('obtiene resumen diario', function () {
    // Preparar

    // Actuar
    $response = $this->getJson('/api/caja/resumen');

    // Verificar
    $response->assertOk();
});
