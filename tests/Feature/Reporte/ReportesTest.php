<?php

/**
 * BDD: docs/specs/reportes.md -> Escenarios R1, R2, R3 (Resumen, reportes por estado, incidencias)
 * @see \App\Http\Controllers\Reportes\ReporteController
 */

use App\Models\Pedido;
use App\Models\Despacho;

uses()->group('reportes');

beforeEach(fn () => actingAsAdmin());

it('obtiene resumen del sistema', function () {
    // Preparar

    // Actuar
    $response = $this->getJson('/api/reportes/resumen');

    // Verificar
    $response->assertOk()
        ->assertJsonStructure(['success', 'data']);
});

it('obtiene pedidos por estado', function () {
    // Preparar
    Pedido::factory()->count(3)->create(['estado' => 'aprobado']);
    Pedido::factory()->count(2)->create(['estado' => 'pendiente']);

    // Actuar
    $response = $this->getJson('/api/reportes/pedidos-por-estado');

    // Verificar
    $response->assertOk();
});

it('obtiene despachos por estado', function () {
    // Preparar
    Despacho::factory()->count(3)->entregado()->create();
    Despacho::factory()->count(1)->create(['estado_despacho' => 'pendiente']);

    // Actuar
    $response = $this->getJson('/api/reportes/despachos-por-estado');

    // Verificar
    $response->assertOk();
});

it('obtiene pedidos por dia', function () {
    // Preparar

    // Actuar
    $response = $this->getJson('/api/reportes/pedidos-por-dia');

    // Verificar
    $response->assertOk();
});

it('obtiene repartidores por estado', function () {
    // Preparar

    // Actuar
    $response = $this->getJson('/api/reportes/repartidores-por-estado');

    // Verificar
    $response->assertOk();
});

it('obtiene vehiculos por estado', function () {
    // Preparar

    // Actuar
    $response = $this->getJson('/api/reportes/vehiculos-por-estado');

    // Verificar
    $response->assertOk();
});

it('obtiene incidencias por tipo', function () {
    // Preparar

    // Actuar
    $response = $this->getJson('/api/reportes/incidencias-por-tipo');

    // Verificar
    $response->assertOk();
});
