<?php

/**
 * BDD: docs/specs/pedidos.md -> Escenarios P1, P2, P3 (CRUD, detalles, cambio de estado)
 * @see \App\Http\Controllers\Pedido\PedidoController
 */

use App\Models\DetallePedido;
use App\Models\Farmacia;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;

uses()->group('pedidos', 'crud');

beforeEach(fn () => actingAsAdmin());

it('lista pedidos', function () {
    // Preparar
    Pedido::factory()->count(3)->create();

    // Actuar
    $response = $this->getJson('/api/pedidos');

    // Verificar
    $response->assertOk();
});

it('crea un pedido', function () {
    // Preparar
    $farmacia = Farmacia::factory()->create();
    $usuario = Usuario::factory()->create();

    // Actuar
    $response = $this->postJson('/api/pedidos', [
        'id_farmacia' => $farmacia->id_farmacia,
        'id_usuario' => $usuario->id_usuario,
        'estado' => 'aprobado',
        'total' => 150.00,
    ]);

    // Verificar
    $response->assertCreated();
});

it('muestra un pedido con detalles', function () {
    // Preparar
    $pedido = Pedido::factory()->create();
    DetallePedido::factory()->count(2)->create(['id_pedido' => $pedido->id_pedido]);

    // Actuar
    $response = $this->getJson("/api/pedidos/{$pedido->id_pedido}");

    // Verificar
    $response->assertOk()
        ->assertJsonStructure(['data' => ['detalles']]);
});

it('actualiza un pedido', function () {
    // Preparar
    $pedido = Pedido::factory()->create();

    // Actuar
    $response = $this->putJson("/api/pedidos/{$pedido->id_pedido}", [
        'estado' => 'cancelado',
    ]);

    // Verificar
    $response->assertOk();
    expect(Pedido::find($pedido->id_pedido)->estado)->toBe('cancelado');
});

it('cambia estado del pedido', function () {
    // Preparar
    $pedido = Pedido::factory()->create();

    // Actuar
    $response = $this->postJson("/api/pedidos/{$pedido->id_pedido}/cambiar-estado", [
        'estado' => 'entregado',
    ]);

    // Verificar
    $response->assertOk();
});

it('elimina un pedido', function () {
    // Preparar
    $pedido = Pedido::factory()->create();

    // Actuar
    $response = $this->deleteJson("/api/pedidos/{$pedido->id_pedido}");

    // Verificar
    $response->assertOk();
});

it('gestiona detalles del pedido', function () {
    // Preparar
    $pedido = Pedido::factory()->create();
    $producto = Producto::factory()->create();

    // Actuar
    $createResponse = $this->postJson("/api/pedidos/{$pedido->id_pedido}/detalles", [
        'id_producto' => $producto->id_producto,
        'cantidad_solicitada' => 10,
        'precio_unitario' => 25.00,
    ]);

    // Verificar
    $createResponse->assertCreated();
});

it('valida datos requeridos del pedido', function () {
    // Preparar

    // Actuar
    $response = $this->postJson('/api/pedidos', []);

    // Verificar
    $response->assertStatus(422);
});
