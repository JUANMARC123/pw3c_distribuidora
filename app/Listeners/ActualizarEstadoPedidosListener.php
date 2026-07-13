<?php

namespace App\Listeners;

use App\Events\RutaIniciada;
use App\Events\EntregaCompletada;
use App\Events\EntregaFallida;
use App\Events\DevolucionProducto;
use App\Models\Pedido;

class ActualizarEstadoPedidosListener
{
    public function onRutaIniciada(RutaIniciada $event)
    {
        Pedido::whereIn('id_pedido', $event->pedidoIds)
            ->where('estado', 'pendiente')
            ->update(['estado' => 'aprobado']);
    }

    public function onEntregaCompletada(EntregaCompletada $event)
    {
        Pedido::where('id_pedido', $event->idPedido)
            ->update(['estado' => 'entregado']);
    }

    public function onEntregaFallida(EntregaFallida $event)
    {
        Pedido::where('id_pedido', $event->idPedido)
            ->update(['estado' => 'pendiente']);
    }

    public function onDevolucionProducto(DevolucionProducto $event)
    {
        Pedido::where('id_pedido', $event->idPedido)
            ->update(['estado' => 'entregado']);
    }

    public function subscribe($events)
    {
        $events->listen(RutaIniciada::class, [self::class, 'onRutaIniciada']);
        $events->listen(EntregaCompletada::class, [self::class, 'onEntregaCompletada']);
        $events->listen(EntregaFallida::class, [self::class, 'onEntregaFallida']);
        $events->listen(DevolucionProducto::class, [self::class, 'onDevolucionProducto']);
    }
}
