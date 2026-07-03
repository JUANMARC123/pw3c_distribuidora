<?php

namespace App\Listeners;

use App\Events\RutaIniciada;
use App\Events\RepartidorRegresa;
use App\Models\Vehiculo\Vehiculo;

class ActualizarEstadoVehiculoListener
{
    public function onRutaIniciada(RutaIniciada $event): void
    {
        Vehiculo::where('id_vehiculo', $event->vehiculoId)
            ->update(['id_estado_vehiculo' => 4]);
    }

    public function onRepartidorRegresa(RepartidorRegresa $event): void
    {
        Vehiculo::where('id_vehiculo', $event->idVehiculo)
            ->update(['id_estado_vehiculo' => 1]);
    }

    public function subscribe($events): array
    {
        return [
            RutaIniciada::class => 'onRutaIniciada',
            RepartidorRegresa::class => 'onRepartidorRegresa',
        ];
    }
}
