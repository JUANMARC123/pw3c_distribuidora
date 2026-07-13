<?php

namespace App\Listeners;

use App\Events\RutaIniciada;
use App\Events\EntregaCompletada;
use App\Events\EntregaFallida;
use App\Events\DevolucionProducto;
use App\Models\Despacho;

class ActualizarEstadoDespachoListener
{
    public function onRutaIniciada(RutaIniciada $event)
    {
        $detalleIds = $event->hojaRuta->detalles()->pluck('id_detalle');

        Despacho::whereIn('id_detalle_hoja_ruta', $detalleIds)
            ->where('estado_despacho', 'pendiente')
            ->update(['estado_despacho' => 'pendiente']);
    }

    public function onEntregaCompletada(EntregaCompletada $event)
    {
        Despacho::where('id_despacho', $event->idDespacho)
            ->update(['estado_despacho' => 'entregado']);
    }

    public function onEntregaFallida(EntregaFallida $event)
    {
        Despacho::where('id_despacho', $event->idDespacho)
            ->update(['estado_despacho' => 'incidencia']);
    }

    public function onDevolucionProducto(DevolucionProducto $event)
    {
        Despacho::where('id_despacho', $event->idDespacho)
            ->update(['estado_despacho' => 'incidencia']);
    }

    public function subscribe($events)
    {
        $events->listen(RutaIniciada::class, [self::class, 'onRutaIniciada']);
        $events->listen(EntregaCompletada::class, [self::class, 'onEntregaCompletada']);
        $events->listen(EntregaFallida::class, [self::class, 'onEntregaFallida']);
        $events->listen(DevolucionProducto::class, [self::class, 'onDevolucionProducto']);
    }
}
