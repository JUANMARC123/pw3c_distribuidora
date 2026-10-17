<?php

namespace Tests\Unit\Logistica\Events;

use App\Events\RepartidorRegresa;
use App\Models\HojaRuta;

it('constructor asigna propiedades', function () {
        $hojaRuta = $this->createMock(HojaRuta::class);
        $idRepartidor = 1;
        $idVehiculo = 5;

        $event = new RepartidorRegresa($hojaRuta, $idRepartidor, $idVehiculo);

        $this->assertSame($hojaRuta, $event->hojaRuta);
        $this->assertSame($idRepartidor, $event->idRepartidor);
        $this->assertSame($idVehiculo, $event->idVehiculo);
    });

    it('usa traits correctos', function () {
        $traits = class_uses(RepartidorRegresa::class);

        $this->assertArrayHasKey(\Illuminate\Foundation\Events\Dispatchable::class, $traits);
        $this->assertArrayHasKey(\Illuminate\Queue\SerializesModels::class, $traits);
    });
