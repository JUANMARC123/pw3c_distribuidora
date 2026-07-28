<?php

namespace Tests\Unit\Despacho\Events;

use App\Events\DevolucionProducto;
use App\Models\HojaRutaDetalle;

it('constructor asigna propiedades', function () {
        $detalle = $this->createMock(HojaRutaDetalle::class);
        $idDespacho = 10;
        $idPedido = 20;

        $event = new DevolucionProducto($detalle, $idDespacho, $idPedido);

        $this->assertSame($detalle, $event->detalle);
        $this->assertSame($idDespacho, $event->idDespacho);
        $this->assertSame($idPedido, $event->idPedido);
    });

    it('usa traits correctos', function () {
        $traits = class_uses(DevolucionProducto::class);

        $this->assertArrayHasKey(\Illuminate\Foundation\Events\Dispatchable::class, $traits);
        $this->assertArrayHasKey(\Illuminate\Queue\SerializesModels::class, $traits);
    });
