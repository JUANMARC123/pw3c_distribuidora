<?php

namespace App\Listeners;

use App\Events\EntregaCompletada;
use App\Events\EntregaFallida;
use App\Events\DevolucionProducto;
use App\Models\HojaRutaDetalle;

class RegistrarLlegadaParadaListener
{
    public function handleEntrega(EntregaCompletada $event)
    {
        if (!$event->detalle->hora_llegada) {
            $event->detalle->update(['hora_llegada' => now()->toTimeString()]);
        }
    }

    public function handleFallida(EntregaFallida $event)
    {
        if (!$event->detalle->hora_llegada) {
            $event->detalle->update(['hora_llegada' => now()->toTimeString()]);
        }
    }

    public function handleDevolucion(DevolucionProducto $event)
    {
        if (!$event->detalle->hora_llegada) {
            $event->detalle->update(['hora_llegada' => now()->toTimeString()]);
        }
    }

    public function subscribe($events)
    {
        $events->listen(EntregaCompletada::class, [self::class, 'handleEntrega']);
        $events->listen(EntregaFallida::class, [self::class, 'handleFallida']);
        $events->listen(DevolucionProducto::class, [self::class, 'handleDevolucion']);
    }
}
