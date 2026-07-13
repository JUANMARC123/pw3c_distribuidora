<?php

use App\Events\EntregaCompletada;
use App\Models\HojaRutaDetalle;

it('constructor asigna propiedades', function () {
        // Preparar
        $detalle = $this->createMock(HojaRutaDetalle::class);
        $idDespacho = 10;
        $idPedido = 20;

        // Actuar
        $event = new EntregaCompletada($detalle, $idDespacho, $idPedido);

        // Verificar
        $this->assertSame($detalle, $event->detalle);
        $this->assertSame($idDespacho, $event->idDespacho);
        $this->assertSame($idPedido, $event->idPedido);
    });

    it('usa traits correctos', function () {
        // Actuar
        $traits = class_uses(EntregaCompletada::class);

        // Verificar
        $this->assertArrayHasKey(\Illuminate\Foundation\Events\Dispatchable::class, $traits);
        $this->assertArrayHasKey(\Illuminate\Queue\SerializesModels::class, $traits);
    });
