<?php

namespace App\Listeners;

use App\Events\RutaIniciada;
use App\Events\RepartidorRegresa;
use App\Models\Repartidor;

class ActualizarEstadoRepartidorListener
{
    public function onRutaIniciada(RutaIniciada $event)
    {
        Repartidor::where('id_repartidor', $event->hojaRuta->id_repartidor)
            ->update(['estado' => 'en_ruta']);
    }

    public function onRepartidorRegresa(RepartidorRegresa $event)
    {
        Repartidor::where('id_repartidor', $event->idRepartidor)
            ->update(['estado' => 'activo']);
    }

    public function subscribe($events)
    {
        $events->listen(RutaIniciada::class, [self::class, 'onRutaIniciada']);
        $events->listen(RepartidorRegresa::class, [self::class, 'onRepartidorRegresa']);
    }
}
