<?php

namespace Tests\Unit\Logistica\Listeners;

use App\Events\RepartidorRegresa;
use App\Events\RutaIniciada;
use App\Listeners\ActualizarEstadoVehiculoListener;
use App\Models\HojaRuta;
use App\Models\Repartidor;
use App\Models\Usuario;
use App\Models\Vehiculo;

beforeEach(function () {
    $this->listener = new ActualizarEstadoVehiculoListener();
});

it('on ruta iniciada cambia a en ruta', function () {
        $vehiculo = Vehiculo::factory()->create(['estado' => 'disponible']);
        $usuario = Usuario::factory()->create();
        $repartidor = Repartidor::factory()->create(['id_usuario' => $usuario->id_usuario]);
        $hojaRuta = HojaRuta::factory()->create([
            'id_repartidor' => $repartidor->id_repartidor,
            'id_vehiculo' => $vehiculo->id_vehiculo,
        ]);

        $event = new RutaIniciada($hojaRuta, [1], $vehiculo->id_vehiculo);

        $this->listener->onRutaIniciada($event);

        $this->assertEquals('en_ruta', $vehiculo->fresh()->estado);
    });

    it('on repartidor regresa cambia a disponible', function () {
        $vehiculo = Vehiculo::factory()->create(['estado' => 'en_ruta']);
        $usuario = Usuario::factory()->create();
        $repartidor = Repartidor::factory()->create(['id_usuario' => $usuario->id_usuario]);
        $hojaRuta = HojaRuta::factory()->create([
            'id_repartidor' => $repartidor->id_repartidor,
            'id_vehiculo' => $vehiculo->id_vehiculo,
        ]);

        $event = new RepartidorRegresa($hojaRuta, $repartidor->id_repartidor, $vehiculo->id_vehiculo);

        $this->listener->onRepartidorRegresa($event);

        $this->assertEquals('disponible', $vehiculo->fresh()->estado);
    });
