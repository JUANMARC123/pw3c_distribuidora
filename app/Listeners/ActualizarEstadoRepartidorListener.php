<?php

namespace App\Listeners;

use App\Events\RutaIniciada;
use App\Events\RepartidorRegresa;
use App\Models\Repartidor\Repartidor;

class ActualizarEstadoRepartidorListener
{
    public function onRutaIniciada(RutaIniciada $event): void
    {
        $repartidorId = $event->controlRuta->id_repartidor;
        Repartidor::where('id_repartidor', $repartidorId)
            ->update(['id_estado_repartidor' => 2]);
    }

    public function onRepartidorRegresa(RepartidorRegresa $event): void
    {
        Repartidor::where('id_repartidor', $event->idRepartidor)
            ->update(['id_estado_repartidor' => 1]);
    }

    public function subscribe($events): array
    {
        return [
            RutaIniciada::class => 'onRutaIniciada',
            RepartidorRegresa::class => 'onRepartidorRegresa',
        ];
    }
}
