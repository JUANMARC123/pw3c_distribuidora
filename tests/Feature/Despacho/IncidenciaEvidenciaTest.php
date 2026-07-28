<?php

/**
 * BDD: docs/specs/despachos.md -> Escenario D4 (Gestion de evidencias e incidencias)
 * @see \App\Http\Controllers\Despacho\EvidenciaController
 * @see \App\Http\Controllers\Despacho\IncidenciaController
 */

use App\Models\Despacho;
use App\Models\Farmacia;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Repartidor;
use App\Models\Vehiculo;
use App\Models\AsignacionVehiculoRepartidor;

uses()->group('despachos', 'incidencias', 'evidencias');

beforeEach(fn () => actingAsAdmin());

it('gestiona incidencias en un despacho', function () {
    // Preparar
    $despacho = Despacho::factory()->create();

    // Actuar
    $createResponse = $this->postJson("/api/despachos/{$despacho->id_despacho}/incidencias", [
        'tipo_incidencia' => 'cliente_ausente',
        'descripcion' => 'No se encontró a nadie en la dirección',
    ]);

    // Verificar
    $createResponse->assertCreated();

    // Actuar
    $listResponse = $this->getJson("/api/despachos/{$despacho->id_despacho}/incidencias");

    // Verificar
    $listResponse->assertOk();
    expect($listResponse->json('data'))->toHaveCount(1);
});

it('gestiona evidencias en un despacho', function () {
    // Preparar
    \Illuminate\Support\Facades\Storage::fake('public');
    $despacho = Despacho::factory()->create();
    $file = \Illuminate\Http\UploadedFile::fake()->create('evidencia.pdf', 1024, 'application/pdf');

    // Actuar
    $createResponse = $this->post("/api/despachos/{$despacho->id_despacho}/evidencias", [
        'tipo_evidencia' => 'foto',
        'archivo' => $file,
    ]);

    // Verificar
    $createResponse->assertCreated();
    expect($createResponse->json('success'))->toBeTrue();

    // Actuar
    $listResponse = $this->getJson("/api/despachos/{$despacho->id_despacho}/evidencias");

    // Verificar
    $listResponse->assertOk();
    expect($listResponse->json('data'))->toHaveCount(1);
});
