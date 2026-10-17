<?php

/**
 * BDD: docs/specs/despachos.md -> Escenario D3 (Transiciones de estado, validaciones, 404)
 * @see \App\Http\Controllers\Despacho\DespachoController::cambiarEstado
 */

use App\Models\Despacho;

uses()->group('despachos', 'estados');

beforeEach(fn () => actingAsAdmin());

it('cambia estado de pendiente a entregado', function () {
    // Preparar
    $despacho = Despacho::factory()->create(['estado_despacho' => 'pendiente']);
    \App\Models\EvidenciaEntrega::factory()->create([
        'id_despacho' => $despacho->id_despacho,
        'tipo_evidencia' => 'foto',
    ]);

    // Actuar
    $response = $this->postJson("/api/despachos/{$despacho->id_despacho}/cambiar-estado", [
        'estado_despacho' => 'entregado',
    ]);

    // Verificar
    $response->assertOk()
        ->assertJsonPath('data.estado_despacho', 'entregado');
    expect(Despacho::find($despacho->id_despacho)->estado_despacho)->toBe('entregado');
});

it('cambia estado de pendiente a incidencia', function () {
    // Preparar
    $despacho = Despacho::factory()->create(['estado_despacho' => 'pendiente']);

    // Actuar
    $response = $this->postJson("/api/despachos/{$despacho->id_despacho}/cambiar-estado", [
        'estado_despacho' => 'incidencia',
    ]);

    // Verificar
    $response->assertOk();
    expect(Despacho::find($despacho->id_despacho)->estado_despacho)->toBe('incidencia');
});

it('cambia estado de entregado a incidencia', function () {
    // Preparar
    $despacho = Despacho::factory()->create(['estado_despacho' => 'entregado']);

    // Actuar
    $response = $this->postJson("/api/despachos/{$despacho->id_despacho}/cambiar-estado", [
        'estado_despacho' => 'incidencia',
    ]);

    // Verificar
    $response->assertOk();
});

it('rechaza estado invalido', function () {
    // Preparar
    $despacho = Despacho::factory()->create();

    // Actuar
    $response = $this->postJson("/api/despachos/{$despacho->id_despacho}/cambiar-estado", [
        'estado_despacho' => 'estado_invalido',
    ]);

    // Verificar
    $response->assertStatus(422);
});

it('requiere campo estado_despacho', function () {
    // Preparar
    $despacho = Despacho::factory()->create();

    // Actuar
    $response = $this->postJson("/api/despachos/{$despacho->id_despacho}/cambiar-estado", []);

    // Verificar
    $response->assertStatus(422);
});

it('404 si el despacho no existe', function () {
    // Preparar

    // Actuar
    $response = $this->postJson('/api/despachos/99999/cambiar-estado', [
        'estado_despacho' => 'entregado',
    ]);

    // Verificar
    $response->assertStatus(404);
});
