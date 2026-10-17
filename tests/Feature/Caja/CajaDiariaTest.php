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
    // Actuar
    $response = $this->postJson('/api/caja/abrir', [
        'id_repartidor' => $this->repartidor->id_repartidor,
        'monto_inicial' => 200.00,
    ]);

    // Verificar
    $response->assertCreated();
});

it('registra un ingreso en caja abierta', function () {
    $cajaResponse = $this->postJson('/api/caja/abrir', [
        'id_repartidor' => $this->repartidor->id_repartidor,
        'monto_inicial' => 200.00,
    ])->assertCreated();
    $caja = $cajaResponse->json('data');

    $response = $this->postJson('/api/caja/ingreso', [
        'id_caja' => $caja['id_caja'],
        'monto' => 350.00,
        'descripcion' => 'Cobro pedido #123',
    ]);

    $response->assertOk();
});

it('registra un gasto en caja abierta', function () {
    $cajaResponse = $this->postJson('/api/caja/abrir', [
        'id_repartidor' => $this->repartidor->id_repartidor,
        'monto_inicial' => 200.00,
    ])->assertCreated();
    $caja = $cajaResponse->json('data');

    $response = $this->postJson('/api/caja/gasto', [
        'id_caja' => $caja['id_caja'],
        'tipo_gasto' => 'COMBUSTIBLE',
        'monto' => 50.00,
        'descripcion' => 'Gasolina',
    ]);

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

it('consulta cajas por periodo mes', function () {
    // Preparar
    CajaRepartidor::factory()->create([
        'id_repartidor' => $this->repartidor->id_repartidor,
        'fecha' => now()->format('Y-m-10'),
    ]);
    CajaRepartidor::factory()->create([
        'id_repartidor' => $this->repartidor->id_repartidor,
        'fecha' => now()->subMonths(2)->format('Y-m-10'),
    ]);

    // Actuar
    $response = $this->getJson('/api/caja/dia?periodo=mes&fecha=' . now()->format('Y-m-01'));

    // Verificar
    $response->assertOk()
        ->assertJsonPath('data.periodo', 'mes')
        ->assertJsonCount(1, 'data.cajas');
});

it('consulta cajas por periodo semana', function () {
    // Preparar
    $lunes = now()->startOfWeek(\Illuminate\Support\Carbon::MONDAY);
    CajaRepartidor::factory()->create([
        'id_repartidor' => $this->repartidor->id_repartidor,
        'fecha' => $lunes->toDateString(),
    ]);
    CajaRepartidor::factory()->create([
        'id_repartidor' => $this->repartidor->id_repartidor,
        'fecha' => $lunes->subWeek()->toDateString(),
    ]);

    // Actuar
    $response = $this->getJson('/api/caja/dia?periodo=semana&fecha=' . $lunes->copy()->addDay()->toDateString());

    // Verificar
    $response->assertOk()
        ->assertJsonPath('data.periodo', 'semana')
        ->assertJsonCount(1, 'data.cajas');
});

it('consulta cajas por periodo año', function () {
    // Preparar
    CajaRepartidor::factory()->create([
        'id_repartidor' => $this->repartidor->id_repartidor,
        'fecha' => now()->format('Y-06-15'),
    ]);
    CajaRepartidor::factory()->create([
        'id_repartidor' => $this->repartidor->id_repartidor,
        'fecha' => now()->subYear()->format('Y-06-15'),
    ]);

    // Actuar
    $response = $this->getJson('/api/caja/dia?periodo=año&fecha=' . now()->format('Y-01-01'));

    // Verificar
    $response->assertOk()
        ->assertJsonPath('data.periodo', 'año')
        ->assertJsonCount(1, 'data.cajas');
});

it('consulta cajas por periodo todos', function () {
    // Preparar
    CajaRepartidor::factory()->create([
        'id_repartidor' => $this->repartidor->id_repartidor,
        'fecha' => now()->subYears(3)->format('Y-m-d'),
    ]);
    CajaRepartidor::factory()->create([
        'id_repartidor' => $this->repartidor->id_repartidor,
        'fecha' => now()->format('Y-m-d'),
    ]);

    // Actuar
    $response = $this->getJson('/api/caja/dia?periodo=todos');

    // Verificar
    $response->assertOk()
        ->assertJsonPath('data.periodo', 'todos')
        ->assertJsonCount(2, 'data.cajas');
});
