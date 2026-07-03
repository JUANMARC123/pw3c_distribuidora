<?php

namespace App\Listeners;

use App\Events\RutaIniciada;
use App\Events\EntregaCompletada;
use App\Events\EntregaFallida;
use App\Events\DevolucionProducto;
use App\Models\Despacho\Despacho;

class ActualizarEstadoDespachoListener
{
    public function onRutaIniciada(RutaIniciada $event): void
    {
        Despacho::whereIn('id_pedido', $event->pedidoIds)
            ->update(['id_estado_despacho' => 2]);
    }

    public function onEntregaCompletada(EntregaCompletada $event): void
    {
        Despacho::where('id_despacho', $event->idDespacho)
            ->update(['id_estado_despacho' => 3]);
    }

    public function onEntregaFallida(EntregaFallida $event): void
    {
        Despacho::where('id_despacho', $event->idDespacho)
            ->update(['id_estado_despacho' => 4]);
    }

    public function onDevolucionProducto(DevolucionProducto $event): void
    {
        Despacho::where('id_despacho', $event->idDespacho)
            ->update(['id_estado_despacho' => 5]);
    }

    public function subscribe($events): array
    {
        return [
            RutaIniciada::class => 'onRutaIniciada',
            EntregaCompletada::class => 'onEntregaCompletada',
            EntregaFallida::class => 'onEntregaFallida',
            DevolucionProducto::class => 'onDevolucionProducto',
        ];
    }
}
