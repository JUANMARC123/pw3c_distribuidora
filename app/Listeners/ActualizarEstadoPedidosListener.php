<?php

namespace App\Listeners;

use App\Events\RutaIniciada;
use App\Events\EntregaCompletada;
use App\Events\EntregaFallida;
use App\Events\DevolucionProducto;
use App\Models\Pedido\Pedido;

class ActualizarEstadoPedidosListener
{
    public function onRutaIniciada(RutaIniciada $event): void
    {
        Pedido::whereIn('id_pedido', $event->pedidoIds)
            ->update(['id_estado_pedido' => 3]);
    }

    public function onEntregaCompletada(EntregaCompletada $event): void
    {
        Pedido::where('id_pedido', $event->idPedido)
            ->update(['id_estado_pedido' => 4]);
    }

    public function onEntregaFallida(EntregaFallida $event): void
    {
        Pedido::where('id_pedido', $event->idPedido)
            ->update(['id_estado_pedido' => 5]);
    }

    public function onDevolucionProducto(DevolucionProducto $event): void
    {
        Pedido::where('id_pedido', $event->idPedido)
            ->update(['id_estado_pedido' => 4]);
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
