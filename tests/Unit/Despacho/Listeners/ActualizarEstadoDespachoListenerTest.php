<?php

namespace Tests\Unit\Despacho\Listeners;

use App\Events\DevolucionProducto;
use App\Events\EntregaCompletada;
use App\Events\EntregaFallida;
use App\Listeners\ActualizarEstadoDespachoListener;
use App\Models\Despacho;
use App\Models\HojaRutaDetalle;

beforeEach(function () {
    $this->listener = new ActualizarEstadoDespachoListener();
});

it('on entrega completada marca entregado', function () {
        $despacho = Despacho::factory()->create(['estado_despacho' => 'pendiente']);

        $detalle = $this->createMock(HojaRutaDetalle::class);
        $event = new EntregaCompletada($detalle, $despacho->id_despacho, 1);

        $this->listener->onEntregaCompletada($event);

        $this->assertEquals('entregado', $despacho->fresh()->estado_despacho);
    });

    it('on entrega fallida marca incidencia', function () {
        $despacho = Despacho::factory()->create(['estado_despacho' => 'pendiente']);

        $detalle = $this->createMock(HojaRutaDetalle::class);
        $event = new EntregaFallida($detalle, $despacho->id_despacho, 1, 'Problema');

        $this->listener->onEntregaFallida($event);

        $this->assertEquals('incidencia', $despacho->fresh()->estado_despacho);
    });

    it('on devolucion producto marca incidencia', function () {
        $despacho = Despacho::factory()->create(['estado_despacho' => 'pendiente']);

        $detalle = $this->createMock(HojaRutaDetalle::class);
        $event = new DevolucionProducto($detalle, $despacho->id_despacho, 1);

        $this->listener->onDevolucionProducto($event);

        $this->assertEquals('incidencia', $despacho->fresh()->estado_despacho);
    });
