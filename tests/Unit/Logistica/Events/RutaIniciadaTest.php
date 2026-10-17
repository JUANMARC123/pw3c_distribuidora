<?php

namespace Tests\Unit\Logistica\Events;

use App\Events\RutaIniciada;
use App\Models\HojaRuta;

it('constructor asigna propiedades', function () {
        $hojaRuta = $this->createMock(HojaRuta::class);
        $pedidoIds = [1, 2, 3];
        $idVehiculo = 5;

        $event = new RutaIniciada($hojaRuta, $pedidoIds, $idVehiculo);

        $this->assertSame($hojaRuta, $event->hojaRuta);
        $this->assertSame($pedidoIds, $event->pedidoIds);
        $this->assertSame($idVehiculo, $event->idVehiculo);
    });

    it('usa traits correctos', function () {
        $traits = class_uses(RutaIniciada::class);

        $this->assertArrayHasKey(
            \Illuminate\Foundation\Events\Dispatchable::class,
            $traits
        );
        $this->assertArrayHasKey(
            \Illuminate\Broadcasting\InteractsWithSockets::class,
            $traits
        );
        $this->assertArrayHasKey(
            \Illuminate\Queue\SerializesModels::class,
            $traits
        );
    });
