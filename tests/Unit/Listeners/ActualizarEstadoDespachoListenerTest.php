<?php

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
        // Preparar
        $despacho = Despacho::factory()->create(['estado_despacho' => 'pendiente']);

        $detalle = $this->createMock(HojaRutaDetalle::class);
        $event = new EntregaCompletada($detalle, $despacho->id_despacho, 1);

        // Actuar
        $this->listener->onEntregaCompletada($event);

        // Verificar
        $this->assertEquals('entregado', $despacho->fresh()->estado_despacho);
    });

    it('on entrega fallida marca incidencia', function () {
        // Preparar
        $despacho = Despacho::factory()->create(['estado_despacho' => 'pendiente']);

        $detalle = $this->createMock(HojaRutaDetalle::class);
        $event = new EntregaFallida($detalle, $despacho->id_despacho, 1, 'Problema');

        // Actuar
        $this->listener->onEntregaFallida($event);

        // Verificar
        $this->assertEquals('incidencia', $despacho->fresh()->estado_despacho);
    });

    it('on devolucion producto marca incidencia', function () {
        // Preparar
        $despacho = Despacho::factory()->create(['estado_despacho' => 'pendiente']);

        $detalle = $this->createMock(HojaRutaDetalle::class);
        $event = new DevolucionProducto($detalle, $despacho->id_despacho, 1);

        // Actuar
        $this->listener->onDevolucionProducto($event);

        // Verificar
        $this->assertEquals('incidencia', $despacho->fresh()->estado_despacho);
    });
