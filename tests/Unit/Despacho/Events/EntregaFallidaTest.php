<?php

namespace Tests\Unit\Despacho\Events;

use App\Events\EntregaFallida;
use App\Models\HojaRutaDetalle;

it('constructor asigna propiedades', function () {
        $detalle = $this->createMock(HojaRutaDetalle::class);
        $idDespacho = 10;
        $idPedido = 20;
        $motivo = 'Cliente ausente';

        $event = new EntregaFallida($detalle, $idDespacho, $idPedido, $motivo);

        $this->assertSame($detalle, $event->detalle);
        $this->assertSame($idDespacho, $event->idDespacho);
        $this->assertSame($idPedido, $event->idPedido);
        $this->assertSame($motivo, $event->motivo);
    });

    it('motivo puede ser vacio', function () {
        $detalle = $this->createMock(HojaRutaDetalle::class);

        $event = new EntregaFallida($detalle, 1, 2, '');

        $this->assertSame('', $event->motivo);
    });

    it('usa traits correctos', function () {
        $traits = class_uses(EntregaFallida::class);

        $this->assertArrayHasKey(\Illuminate\Foundation\Events\Dispatchable::class, $traits);
        $this->assertArrayHasKey(\Illuminate\Queue\SerializesModels::class, $traits);
    });
