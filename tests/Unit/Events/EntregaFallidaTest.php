<?php

use App\Events\EntregaFallida;
use App\Models\HojaRutaDetalle;

it('constructor asigna propiedades', function () {
        // Preparar
        $detalle = $this->createMock(HojaRutaDetalle::class);
        $idDespacho = 10;
        $idPedido = 20;
        $motivo = 'Cliente ausente';

        // Actuar
        $event = new EntregaFallida($detalle, $idDespacho, $idPedido, $motivo);

        // Verificar
        $this->assertSame($detalle, $event->detalle);
        $this->assertSame($idDespacho, $event->idDespacho);
        $this->assertSame($idPedido, $event->idPedido);
        $this->assertSame($motivo, $event->motivo);
    });

    it('motivo puede ser vacio', function () {
        // Preparar
        $detalle = $this->createMock(HojaRutaDetalle::class);

        // Actuar
        $event = new EntregaFallida($detalle, 1, 2, '');

        // Verificar
        $this->assertSame('', $event->motivo);
    });

    it('usa traits correctos', function () {
        // Actuar
        $traits = class_uses(EntregaFallida::class);

        // Verificar
        $this->assertArrayHasKey(\Illuminate\Foundation\Events\Dispatchable::class, $traits);
        $this->assertArrayHasKey(\Illuminate\Queue\SerializesModels::class, $traits);
    });
