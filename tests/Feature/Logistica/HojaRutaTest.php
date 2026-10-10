<?php

/**
 * BDD: docs/specs/logistica.md -> Escenarios L3, L4 (Hojas de ruta, ciclo completo, consultas)
 * @see \App\Http\Controllers\Logistica\ControlRutaController
 * @see \App\Http\Controllers\Logistica\CentroLogisticoController
 */

use App\Models\HojaRuta;
use App\Models\Despacho;
use App\Models\Farmacia;
use App\Models\Pedido;
use App\Models\Repartidor;
use App\Models\Vehiculo;
use App\Models\AsignacionVehiculoRepartidor;

uses()->group('hojas-ruta', 'logistica');

beforeEach(fn () => actingAsAdmin());

it('lista hojas de ruta', function () {
    // Preparar
    HojaRuta::factory()->count(3)->create();

    // Actuar
    $response = $this->getJson('/api/hojas-ruta');

    // Verificar
    $response->assertOk();
});

it('crea una hoja de ruta con pedidos', function () {
    // Preparar
    $farmacia = Farmacia::factory()->create();
    $repartidor = Repartidor::factory()->create();
    $vehiculo = Vehiculo::factory()->create();
    AsignacionVehiculoRepartidor::factory()->create([
        'id_repartidor' => $repartidor->id_repartidor,
        'id_vehiculo' => $vehiculo->id_vehiculo,
    ]);

    $pedidoResponse = $this->postJson('/api/pedidos', [
        'id_farmacia' => $farmacia->id_farmacia,
        'id_usuario' => $repartidor->usuario->id_usuario,
        'estado' => 'pendiente',
        'total' => 200.00,
    ])->assertCreated();
    $pedido = $pedidoResponse->json('data');

    // Actuar
    $response = $this->postJson('/api/hojas-ruta', [
        'fecha' => now()->format('Y-m-d'),
        'id_repartidor' => $repartidor->id_repartidor,
        'id_vehiculo' => $vehiculo->id_vehiculo,
        'pedidos' => [
            ['id_pedido' => $pedido['id_pedido'], 'orden_visita' => 1],
        ],
    ]);

    // Verificar
    $response->assertCreated()
        ->assertJsonStructure(['data' => ['detalles']]);
});

it('inicia una ruta planificada', function () {
    // Preparar
    $repartidor = Repartidor::factory()->create();
    $vehiculo = Vehiculo::factory()->create();
    $farmacia = Farmacia::factory()->create();
    AsignacionVehiculoRepartidor::factory()->create([
        'id_repartidor' => $repartidor->id_repartidor,
        'id_vehiculo' => $vehiculo->id_vehiculo,
    ]);

    $pedidoResponse = $this->postJson('/api/pedidos', [
        'id_farmacia' => $farmacia->id_farmacia,
        'id_usuario' => $repartidor->usuario->id_usuario,
        'estado' => 'pendiente',
        'total' => 100.00,
    ])->assertCreated();

    $hojaRutaResponse = $this->postJson('/api/hojas-ruta', [
        'fecha' => now()->format('Y-m-d'),
        'id_repartidor' => $repartidor->id_repartidor,
        'id_vehiculo' => $vehiculo->id_vehiculo,
        'pedidos' => [
            ['id_pedido' => $pedidoResponse->json('data.id_pedido'), 'orden_visita' => 1],
        ],
    ])->assertCreated();
    $hojaRutaId = $hojaRutaResponse->json('data.id_hoja_ruta');

    // Actuar
    $response = $this->postJson("/api/hojas-ruta/{$hojaRutaId}/iniciar");

    // Verificar
    $response->assertOk();
    expect(HojaRuta::find($hojaRutaId)->estado_ruta)->toBe('en_progreso');
});

it('rechaza iniciar ruta no planificada', function () {
    // Preparar
    $hojaRuta = HojaRuta::factory()->enProgreso()->create();

    // Actuar
    $response = $this->postJson("/api/hojas-ruta/{$hojaRuta->id_hoja_ruta}/iniciar");

    // Verificar
    $response->assertStatus(422);
});

it('obtiene pedidos pendientes', function () {
    // Preparar
    $farmacia = Farmacia::factory()->create();
    $pedido = Pedido::factory()->create(['estado' => 'pendiente']);

    // Actuar
    $response = $this->getJson('/api/logistica/pedidos-pendientes');

    // Verificar
    $response->assertOk();
    $pedidos = $response->json('data');
    expect(collect($pedidos)->where('id_pedido', $pedido->id_pedido))->not->toBeEmpty();
});

it('obtiene repartidores disponibles', function () {
    // Preparar
    Repartidor::factory()->count(2)->create();

    // Actuar
    $response = $this->getJson('/api/logistica/repartidores-disponibles');

    // Verificar
    $response->assertOk();
});

it('obtiene rutas activas', function () {
    // Preparar
    HojaRuta::factory()->enProgreso()->create();

    // Actuar
    $response = $this->getJson('/api/logistica/rutas-activas');

    // Verificar
    $response->assertOk();
});
