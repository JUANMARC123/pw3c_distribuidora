<?php

namespace App\Listeners;

use App\Events\RutaIniciada;
use App\Events\RepartidorRegresa;
use App\Models\Vehiculo;

class ActualizarEstadoVehiculoListener
{
    public function onRutaIniciada(RutaIniciada $event)
    {
        Vehiculo::where('id_vehiculo', $event->idVehiculo)
            ->update(['estado' => 'en_ruta']);
    }

    public function onRepartidorRegresa(RepartidorRegresa $event)
    {
        Vehiculo::where('id_vehiculo', $event->idVehiculo)
            ->update(['estado' => 'disponible']);
    }

    public function subscribe($events)
    {
        $events->listen(RutaIniciada::class, [self::class, 'onRutaIniciada']);
        $events->listen(RepartidorRegresa::class, [self::class, 'onRepartidorRegresa']);
    }
}
