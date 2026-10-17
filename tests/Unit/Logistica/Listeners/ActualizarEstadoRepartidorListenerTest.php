<?php

namespace Tests\Unit\Logistica\Listeners;

use App\Events\RepartidorRegresa;
use App\Events\RutaIniciada;
use App\Listeners\ActualizarEstadoRepartidorListener;
use App\Models\HojaRuta;
use App\Models\Repartidor;
use App\Models\Usuario;
use App\Models\Vehiculo;

beforeEach(function () {
    $this->listener = new ActualizarEstadoRepartidorListener();
});

it('on ruta iniciada cambia a en ruta', function () {
        $usuario = Usuario::factory()->create();
        $repartidor = Repartidor::factory()->create([
            'id_usuario' => $usuario->id_usuario,
            'estado' => 'activo',
        ]);
        $vehiculo = Vehiculo::factory()->create();
        $hojaRuta = HojaRuta::factory()->create([
            'id_repartidor' => $repartidor->id_repartidor,
            'id_vehiculo' => $vehiculo->id_vehiculo,
        ]);

        $event = new RutaIniciada($hojaRuta, [1], $vehiculo->id_vehiculo);

        $this->listener->onRutaIniciada($event);

        $this->assertEquals('en_ruta', $repartidor->fresh()->estado);
    });

    it('on repartidor regresa cambia a activo', function () {
        $usuario = Usuario::factory()->create();
        $repartidor = Repartidor::factory()->create([
            'id_usuario' => $usuario->id_usuario,
            'estado' => 'en_ruta',
        ]);
        $vehiculo = Vehiculo::factory()->create();
        $hojaRuta = HojaRuta::factory()->create([
            'id_repartidor' => $repartidor->id_repartidor,
            'id_vehiculo' => $vehiculo->id_vehiculo,
        ]);

        $event = new RepartidorRegresa($hojaRuta, $repartidor->id_repartidor, $vehiculo->id_vehiculo);

        $this->listener->onRepartidorRegresa($event);

        $this->assertEquals('activo', $repartidor->fresh()->estado);
    });
