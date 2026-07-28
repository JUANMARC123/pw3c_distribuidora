<?php

/**
 * BDD: docs/specs/catalogos.md -> Escenario C3 (CRUD de productos)
 * @see \App\Http\Controllers\Producto\ProductoController
 */

use App\Models\Producto;

uses()->group('productos', 'crud');

beforeEach(fn () => actingAsAdmin());

it('lista productos', function () {
    // Preparar
    Producto::factory()->count(3)->create();

    // Actuar
    $response = $this->getJson('/api/productos');

    // Verificar
    $response->assertOk();
});

it('crea un producto', function () {
    // Preparar

    // Actuar
    $response = $this->postJson('/api/productos', [
        'nombre' => 'Paracetamol',
        'precio_unitario' => 25.50,
        'unidad_medida' => 'CAJA',
    ]);

    // Verificar
    $response->assertCreated()
        ->assertJsonPath('data.nombre', 'Paracetamol');
});

it('muestra un producto', function () {
    // Preparar
    $producto = Producto::factory()->create();

    // Actuar
    $response = $this->getJson("/api/productos/{$producto->id_producto}");

    // Verificar
    $response->assertOk();
});

it('actualiza un producto', function () {
    // Preparar
    $producto = Producto::factory()->create();

    // Actuar
    $response = $this->putJson("/api/productos/{$producto->id_producto}", [
        'nombre' => 'Ibuprofeno',
    ]);

    // Verificar
    $response->assertOk();
    expect(Producto::find($producto->id_producto)->nombre)->toBe('Ibuprofeno');
});

it('elimina un producto', function () {
    // Preparar
    $producto = Producto::factory()->create();

    // Actuar
    $response = $this->deleteJson("/api/productos/{$producto->id_producto}");

    // Verificar
    $response->assertOk();
});

it('valida datos requeridos del producto', function () {
    // Preparar

    // Actuar
    $response = $this->postJson('/api/productos', []);

    // Verificar
    $response->assertStatus(422);
});
