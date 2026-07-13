<?php

use App\Events\RepartidorRegresa;
use App\Models\HojaRuta;

it('constructor asigna propiedades', function () {
        // Preparar
        $hojaRuta = $this->createMock(HojaRuta::class);
        $idRepartidor = 1;
        $idVehiculo = 5;

        // Actuar
        $event = new RepartidorRegresa($hojaRuta, $idRepartidor, $idVehiculo);

        // Verificar
        $this->assertSame($hojaRuta, $event->hojaRuta);
        $this->assertSame($idRepartidor, $event->idRepartidor);
        $this->assertSame($idVehiculo, $event->idVehiculo);
    });

    it('usa traits correctos', function () {
        // Actuar
        $traits = class_uses(RepartidorRegresa::class);

        // Verificar
        $this->assertArrayHasKey(\Illuminate\Foundation\Events\Dispatchable::class, $traits);
        $this->assertArrayHasKey(\Illuminate\Queue\SerializesModels::class, $traits);
    });
