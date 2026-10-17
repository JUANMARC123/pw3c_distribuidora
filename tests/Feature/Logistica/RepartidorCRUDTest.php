<?php

/**
 * BDD: docs/specs/logistica.md -> Escenario L1 (CRUD de repartidores, asignacion vehiculo)
 * @see \App\Http\Controllers\Repartidor\RepartidorController
 */

use App\Models\Repartidor;
use App\Models\Usuario;

uses()->group('repartidores', 'crud');

beforeEach(fn () => actingAsAdmin());

it('lista repartidores', function () {
    // Preparar
    Repartidor::factory()->count(3)->create();

    // Actuar
    $response = $this->getJson('/api/repartidores');

    // Verificar
    $response->assertOk();
});

it('crea un repartidor', function () {
    // Preparar
    $usuario = Usuario::factory()->create();

    // Actuar
    $response = $this->postJson('/api/repartidores', [
        'id_usuario' => $usuario->id_usuario,
        'ci' => '12345678',
        'categoria_licencia' => 'B',
    ]);

    // Verificar
    $response->assertCreated();
});

it('muestra un repartidor', function () {
    // Preparar
    $repartidor = Repartidor::factory()->create();

    // Actuar
    $response = $this->getJson("/api/repartidores/{$repartidor->id_repartidor}");

    // Verificar
    $response->assertOk();
});

it('actualiza un repartidor', function () {
    // Preparar
    $repartidor = Repartidor::factory()->create();

    // Actuar
    $response = $this->putJson("/api/repartidores/{$repartidor->id_repartidor}", [
        'categoria_licencia' => 'C',
    ]);

    // Verificar
    $response->assertOk();
    expect(Repartidor::find($repartidor->id_repartidor)->categoria_licencia)->toBe('C');
});

it('cambia estado del repartidor', function () {
    // Preparar
    $repartidor = Repartidor::factory()->create();

    // Actuar
    $response = $this->postJson("/api/repartidores/{$repartidor->id_repartidor}/cambiar-estado", [
        'estado' => 'inactivo',
    ]);

    // Verificar
    $response->assertOk();
});

it('elimina un repartidor', function () {
    // Preparar
    $repartidor = Repartidor::factory()->create();

    // Actuar
    $response = $this->deleteJson("/api/repartidores/{$repartidor->id_repartidor}");

    // Verificar
    $response->assertOk();
});

it('asigna vehiculo al repartidor', function () {
    // Preparar
    $vehiculo = \App\Models\Vehiculo::factory()->create();
    $repartidor = Repartidor::factory()->create();

    // Actuar
    $response = $this->postJson("/api/repartidores/{$repartidor->id_repartidor}/asignacion-vehiculo", [
        'id_vehiculo' => $vehiculo->id_vehiculo,
    ]);

    // Verificar
    $response->assertCreated();
});

it('remueve vehiculo del repartidor', function () {
    // Preparar
    $asignacion = \App\Models\AsignacionVehiculoRepartidor::factory()->create();

    // Actuar
    $response = $this->deleteJson("/api/repartidores/{$asignacion->id_repartidor}/asignacion-vehiculo");

    // Verificar
    $response->assertOk();
});
