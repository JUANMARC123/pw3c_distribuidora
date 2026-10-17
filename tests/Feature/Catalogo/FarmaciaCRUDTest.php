<?php

/**
 * BDD: docs/specs/catalogos.md -> Escenario C2 (CRUD de farmacias con contactos)
 * @see \App\Http\Controllers\Farmacia\FarmaciaController
 * @see \App\Http\Controllers\Farmacia\ContactoFarmaciaController
 */

use App\Models\ContactoFarmacia;
use App\Models\Farmacia;

uses()->group('farmacias', 'crud');

beforeEach(fn () => actingAsAdmin());

it('lista farmacias', function () {
    // Preparar
    Farmacia::factory()->count(3)->create();

    // Actuar
    $response = $this->getJson('/api/farmacias');

    // Verificar
    $response->assertOk();
});

it('crea una farmacia', function () {
    // Preparar

    // Actuar
    $response = $this->postJson('/api/farmacias', [
        'nombre' => 'Farmacia Test',
        'direccion' => 'Calle 123',
        'telefono' => '72222222',
        'email' => 'farm@test.com',
        'latitud' => -16.5,
        'longitud' => -68.1,
    ]);

    // Verificar
    $response->assertCreated()
        ->assertJsonPath('data.nombre', 'Farmacia Test');
});

it('muestra una farmacia', function () {
    // Preparar
    $farmacia = Farmacia::factory()->create();

    // Actuar
    $response = $this->getJson("/api/farmacias/{$farmacia->id_farmacia}");

    // Verificar
    $response->assertOk();
});

it('actualiza una farmacia', function () {
    // Preparar
    $farmacia = Farmacia::factory()->create();

    // Actuar
    $response = $this->putJson("/api/farmacias/{$farmacia->id_farmacia}", [
        'nombre' => 'Actualizada',
    ]);

    // Verificar
    $response->assertOk();
    expect(Farmacia::find($farmacia->id_farmacia)->nombre)->toBe('Actualizada');
});

it('elimina una farmacia', function () {
    // Preparar
    $farmacia = Farmacia::factory()->create();

    // Actuar
    $response = $this->deleteJson("/api/farmacias/{$farmacia->id_farmacia}");

    // Verificar
    $response->assertOk();
});

it('gestiona contactos de farmacia', function () {
    // Preparar
    $farmacia = Farmacia::factory()->create();

    // Actuar
    $createResponse = $this->postJson("/api/farmacias/{$farmacia->id_farmacia}/contactos", [
        'nombre_contacto' => 'Contacto Test',
        'telefono' => '73333333',
        'cargo' => 'Gerente',
    ]);

    // Verificar
    $createResponse->assertCreated();

    // Actuar
    $listResponse = $this->getJson("/api/farmacias/{$farmacia->id_farmacia}/contactos");

    // Verificar
    $listResponse->assertOk();
    expect($listResponse->json('data'))->toHaveCount(1);
});

it('valida datos requeridos al crear farmacia', function () {
    // Preparar

    // Actuar
    $response = $this->postJson('/api/farmacias', []);

    // Verificar
    $response->assertStatus(422);
});
