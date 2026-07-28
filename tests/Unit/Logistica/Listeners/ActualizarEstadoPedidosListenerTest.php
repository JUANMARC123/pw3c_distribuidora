<?php

namespace Tests\Unit\Logistica\Listeners;

use App\Events\DevolucionProducto;
use App\Events\EntregaCompletada;
use App\Events\EntregaFallida;
use App\Events\RutaIniciada;
use App\Listeners\ActualizarEstadoPedidosListener;
use App\Models\Farmacia;
use App\Models\HojaRuta;
use App\Models\HojaRutaDetalle;
use App\Models\Pedido;
use App\Models\Repartidor;
use App\Models\Usuario;
use App\Models\Vehiculo;

beforeEach(function () {
    $this->listener = new ActualizarEstadoPedidosListener();
});

it('on ruta iniciada aprueba pedidos pendientes', function () {
        $farmacia = Farmacia::factory()->create();
        $usuario = Usuario::factory()->create();

        $pedido1 = Pedido::factory()->create([
            'id_farmacia' => $farmacia->id_farmacia,
            'id_usuario' => $usuario->id_usuario,
            'estado' => 'pendiente',
        ]);
        $pedido2 = Pedido::factory()->create([
            'id_farmacia' => $farmacia->id_farmacia,
            'id_usuario' => $usuario->id_usuario,
            'estado' => 'pendiente',
        ]);
        $pedido3 = Pedido::factory()->create([
            'id_farmacia' => $farmacia->id_farmacia,
            'id_usuario' => $usuario->id_usuario,
            'estado' => 'aprobado',
        ]);

        $repartidor = Repartidor::factory()->create(['id_usuario' => $usuario->id_usuario]);
        $vehiculo = Vehiculo::factory()->create();
        $hojaRuta = HojaRuta::factory()->create([
            'id_repartidor' => $repartidor->id_repartidor,
            'id_vehiculo' => $vehiculo->id_vehiculo,
        ]);

        $event = new RutaIniciada($hojaRuta, [$pedido1->id_pedido, $pedido2->id_pedido], $vehiculo->id_vehiculo);

        $this->listener->onRutaIniciada($event);

        $this->assertEquals('aprobado', $pedido1->fresh()->estado);
        $this->assertEquals('aprobado', $pedido2->fresh()->estado);
        $this->assertEquals('aprobado', $pedido3->fresh()->estado);
    });

    it('on entrega completada marca entregado', function () {
        $farmacia = Farmacia::factory()->create();
        $usuario = Usuario::factory()->create();
        $pedido = Pedido::factory()->create([
            'id_farmacia' => $farmacia->id_farmacia,
            'id_usuario' => $usuario->id_usuario,
            'estado' => 'aprobado',
        ]);

        $detalle = $this->createMock(HojaRutaDetalle::class);
        $event = new EntregaCompletada($detalle, 1, $pedido->id_pedido);

        $this->listener->onEntregaCompletada($event);

        $this->assertEquals('entregado', $pedido->fresh()->estado);
    });

    it('on entrega fallida revierte a pendiente', function () {
        $farmacia = Farmacia::factory()->create();
        $usuario = Usuario::factory()->create();
        $pedido = Pedido::factory()->create([
            'id_farmacia' => $farmacia->id_farmacia,
            'id_usuario' => $usuario->id_usuario,
            'estado' => 'aprobado',
        ]);

        $detalle = $this->createMock(HojaRutaDetalle::class);
        $event = new EntregaFallida($detalle, 1, $pedido->id_pedido, 'Cliente ausente');

        $this->listener->onEntregaFallida($event);

        $this->assertEquals('pendiente', $pedido->fresh()->estado);
    });

    it('on devolucion producto marca entregado', function () {
        $farmacia = Farmacia::factory()->create();
        $usuario = Usuario::factory()->create();
        $pedido = Pedido::factory()->create([
            'id_farmacia' => $farmacia->id_farmacia,
            'id_usuario' => $usuario->id_usuario,
            'estado' => 'aprobado',
        ]);

        $detalle = $this->createMock(HojaRutaDetalle::class);
        $event = new DevolucionProducto($detalle, 1, $pedido->id_pedido);

        $this->listener->onDevolucionProducto($event);

        $this->assertEquals('entregado', $pedido->fresh()->estado);
    });
