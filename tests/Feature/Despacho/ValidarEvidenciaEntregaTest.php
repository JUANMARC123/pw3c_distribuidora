<?php

/**
 * =========================================================================
 * BDD: docs/specs/despachos.md -> Escenario D1 y D2
 * @see \App\Http\Controllers\Despacho\DespachoController::cambiarEstado
 * =========================================================================
 */

use App\Models\Despacho;
use App\Models\EvidenciaEntrega;

uses()->group('despacho', 'evidencia', 'bdd');

beforeEach(fn () => actingAsAdmin());

it('Escenario 1: Bloquea la finalizacion si el despacho no tiene ninguna evidencia', function () {
    $despacho = Despacho::factory()->create(['estado_despacho' => 'pendiente']);

    $response = $this->postJson("/api/despachos/{$despacho->id_despacho}/cambiar-estado", [
        'estado_despacho' => 'entregado',
    ]);

    $response->assertStatus(422);
    $response->assertJsonFragment([
        'error' => 'Debe subir al menos una foto como evidencia antes de finalizar la entrega.'
    ]);
});

it('Escenario 2: Permite completar la entrega si el despacho ya tiene una evidencia registrada', function () {
    $despacho = Despacho::factory()->create(['estado_despacho' => 'pendiente']);
    EvidenciaEntrega::factory()->create([
        'id_despacho' => $despacho->id_despacho,
        'tipo_evidencia' => 'foto',
    ]);

    $response = $this->postJson("/api/despachos/{$despacho->id_despacho}/cambiar-estado", [
        'estado_despacho' => 'entregado',
    ]);

    $response->assertOk();
    expect(Despacho::find($despacho->id_despacho)->estado_despacho)->toBe('entregado');
});
