<?php

/**
 * BDD: docs/specs/logistica.md -> Escenario L2 (CRUD de vehiculos, placa unica, cambio estado)
 * @see \App\Http\Controllers\Vehiculo\VehiculoController
 */

use App\Models\Vehiculo;

uses()->group('vehiculos', 'crud');

beforeEach(fn () => actingAsAdmin());

it('lista vehiculos', function () {
    // Preparar
    Vehiculo::factory()->count(3)->create();

    // Actuar
    $response = $this->getJson('/api/vehiculos');

    // Verificar
    $response->assertOk();
});

it('crea un vehiculo', function () {
    // Preparar

    // Actuar
    $response = $this->postJson('/api/vehiculos', [
        'placa' => '1234ABC',
        'marca' => 'Toyota',
        'modelo' => 'Hilux',
    ]);

    // Verificar
    $response->assertCreated()
        ->assertJsonPath('data.placa', '1234ABC');
});

it('muestra un vehiculo', function () {
    // Preparar
    $vehiculo = Vehiculo::factory()->create();

    // Actuar
    $response = $this->getJson("/api/vehiculos/{$vehiculo->id_vehiculo}");

    // Verificar
    $response->assertOk();
});

it('actualiza un vehiculo', function () {
    // Preparar
    $vehiculo = Vehiculo::factory()->create();

    // Actuar
    $response = $this->putJson("/api/vehiculos/{$vehiculo->id_vehiculo}", [
        'estado' => 'taller',
    ]);

    // Verificar
    $response->assertOk();
    expect(Vehiculo::find($vehiculo->id_vehiculo)->estado)->toBe('taller');
});

it('cambia estado del vehiculo', function () {
    // Preparar
    $vehiculo = Vehiculo::factory()->create();

    // Actuar
    $response = $this->postJson("/api/vehiculos/{$vehiculo->id_vehiculo}/cambiar-estado", [
        'estado' => 'taller',
    ]);

    // Verificar
    $response->assertOk();
});

it('elimina un vehiculo', function () {
    // Preparar
    $vehiculo = Vehiculo::factory()->create();

    // Actuar
    $response = $this->deleteJson("/api/vehiculos/{$vehiculo->id_vehiculo}");

    // Verificar
    $response->assertOk();
});

it('valida placa unica', function () {
    // Preparar
    Vehiculo::factory()->create(['placa' => '9999ZZZ']);

    // Actuar
    $response = $this->postJson('/api/vehiculos', [
        'placa' => '9999ZZZ',
        'marca' => 'Test',
        'modelo' => 'Test',
    ]);

    // Verificar
    $response->assertStatus(422);
});
