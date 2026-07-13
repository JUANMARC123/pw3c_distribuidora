<?php

use App\Models\Despacho;
use App\Models\Farmacia;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Repartidor;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Models\AsignacionVehiculoRepartidor;

uses()->group('despachos', 'ciclo-completo');

beforeEach(fn () => actingAsAdmin());

it('completa el ciclo de vida completo de un despacho vía eventos', function () {
    // Preparar
    $farmacia = Farmacia::factory()->create();
    $producto = Producto::factory()->create();
    $repartidor = Repartidor::factory()->create();
    $vehiculo = Vehiculo::factory()->create();
    AsignacionVehiculoRepartidor::factory()->create([
        'id_repartidor' => $repartidor->id_repartidor,
        'id_vehiculo' => $vehiculo->id_vehiculo,
    ]);

    $pedidoResponse = $this->postJson('/api/pedidos', [
        'id_farmacia' => $farmacia->id_farmacia,
        'id_usuario' => $repartidor->usuario->id_usuario,
        'estado' => 'aprobado',
        'total' => 150.00,
    ])->assertCreated();
    $pedido = $pedidoResponse->json('data');

    $hojaRutaResponse = $this->postJson('/api/hojas-ruta', [
        'fecha' => now()->format('Y-m-d'),
        'id_repartidor' => $repartidor->id_repartidor,
        'id_vehiculo' => $vehiculo->id_vehiculo,
        'pedidos' => [
            ['id_pedido' => $pedido['id_pedido'], 'orden_visita' => 1],
        ],
    ])->assertCreated();
    $hojaRuta = $hojaRutaResponse->json('data');

    $despacho = Despacho::where('id_pedido', $pedido['id_pedido'])->first();
    expect($despacho)->not->toBeNull();
    expect($despacho->estado_despacho)->toBe('pendiente');

    // Actuar
    $this->postJson("/api/hojas-ruta/{$hojaRuta['id_hoja_ruta']}/iniciar")
        ->assertOk();

    $detalle = $hojaRuta['detalles'][0];

    // Actuar
    $this->postJson("/api/hojas-ruta/{$hojaRuta['id_hoja_ruta']}/registrar-llegada", [
        'id_detalle' => $detalle['id_detalle'],
    ])->assertOk();

    // Actuar
    $this->postJson("/api/hojas-ruta/{$hojaRuta['id_hoja_ruta']}/entrega-completada", [
        'id_detalle' => $detalle['id_detalle'],
        'id_despacho' => $despacho->id_despacho,
        'id_pedido' => $pedido['id_pedido'],
    ])->assertOk();

    // Verificar
    $despacho->refresh();
    expect($despacho->estado_despacho)->toBe('entregado');

    // Actuar
    $this->postJson("/api/hojas-ruta/{$hojaRuta['id_hoja_ruta']}/regreso-almacen")
        ->assertOk();
});

it('registra incidencia en un despacho', function () {
    // Preparar
    $farmacia = Farmacia::factory()->create();
    $repartidor = Repartidor::factory()->create();
    $vehiculo = Vehiculo::factory()->create();
    AsignacionVehiculoRepartidor::factory()->create([
        'id_repartidor' => $repartidor->id_repartidor,
        'id_vehiculo' => $vehiculo->id_vehiculo,
    ]);

    $pedidoResponse = $this->postJson('/api/pedidos', [
        'id_farmacia' => $farmacia->id_farmacia,
        'id_usuario' => $repartidor->usuario->id_usuario,
        'estado' => 'aprobado',
        'total' => 100.00,
    ])->assertCreated();
    $pedido = $pedidoResponse->json('data');

    $hojaRutaResponse = $this->postJson('/api/hojas-ruta', [
        'fecha' => now()->format('Y-m-d'),
        'id_repartidor' => $repartidor->id_repartidor,
        'id_vehiculo' => $vehiculo->id_vehiculo,
        'pedidos' => [
            ['id_pedido' => $pedido['id_pedido'], 'orden_visita' => 1],
        ],
    ])->assertCreated();
    $hojaRuta = $hojaRutaResponse->json('data');

    $despacho = Despacho::where('id_pedido', $pedido['id_pedido'])->first();
    $detalle = $hojaRuta['detalles'][0];

    // Actuar
    $this->postJson("/api/hojas-ruta/{$hojaRuta['id_hoja_ruta']}/entrega-fallida", [
        'id_detalle' => $detalle['id_detalle'],
        'id_despacho' => $despacho->id_despacho,
        'id_pedido' => $pedido['id_pedido'],
        'motivo' => 'Cliente ausente',
    ])->assertOk();

    // Verificar
    $despacho->refresh();
    expect($despacho->estado_despacho)->toBe('incidencia');
});

it('registra devolucion en un despacho', function () {
    // Preparar
    $farmacia = Farmacia::factory()->create();
    $repartidor = Repartidor::factory()->create();
    $vehiculo = Vehiculo::factory()->create();
    AsignacionVehiculoRepartidor::factory()->create([
        'id_repartidor' => $repartidor->id_repartidor,
        'id_vehiculo' => $vehiculo->id_vehiculo,
    ]);

    $pedidoResponse = $this->postJson('/api/pedidos', [
        'id_farmacia' => $farmacia->id_farmacia,
        'id_usuario' => $repartidor->usuario->id_usuario,
        'estado' => 'aprobado',
        'total' => 100.00,
    ])->assertCreated();
    $pedido = $pedidoResponse->json('data');

    $hojaRutaResponse = $this->postJson('/api/hojas-ruta', [
        'fecha' => now()->format('Y-m-d'),
        'id_repartidor' => $repartidor->id_repartidor,
        'id_vehiculo' => $vehiculo->id_vehiculo,
        'pedidos' => [
            ['id_pedido' => $pedido['id_pedido'], 'orden_visita' => 1],
        ],
    ])->assertCreated();
    $hojaRuta = $hojaRutaResponse->json('data');

    $despacho = Despacho::where('id_pedido', $pedido['id_pedido'])->first();
    $detalle = $hojaRuta['detalles'][0];

    // Actuar
    $this->postJson("/api/hojas-ruta/{$hojaRuta['id_hoja_ruta']}/devolucion", [
        'id_detalle' => $detalle['id_detalle'],
        'id_despacho' => $despacho->id_despacho,
        'id_pedido' => $pedido['id_pedido'],
    ])->assertOk();

    // Verificar
    $despacho->refresh();
    expect($despacho->estado_despacho)->toBe('incidencia');
});
