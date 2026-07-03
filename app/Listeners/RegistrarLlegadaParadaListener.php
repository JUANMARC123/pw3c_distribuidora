<?php

namespace App\Listeners;

use App\Events\EntregaCompletada;
use App\Events\EntregaFallida;
use App\Events\DevolucionProducto;
use Illuminate\Support\Facades\DB;

class RegistrarLlegadaParadaListener
{
    public function onEntregaCompletada(EntregaCompletada $event): void
    {
        DB::table('control_ruta_paradas')
            ->where('id_control_ruta_parada', $event->parada->id_control_ruta_parada)
            ->update(['hora_llegada' => now()->toTimeString()]);
    }

    public function onEntregaFallida(EntregaFallida $event): void
    {
        DB::table('control_ruta_paradas')
            ->where('id_control_ruta_parada', $event->parada->id_control_ruta_parada)
            ->update(['hora_llegada' => now()->toTimeString()]);
    }

    public function onDevolucionProducto(DevolucionProducto $event): void
    {
        DB::table('control_ruta_paradas')
            ->where('id_control_ruta_parada', $event->parada->id_control_ruta_parada)
            ->update(['hora_llegada' => now()->toTimeString()]);
    }

    public function subscribe($events): array
    {
        return [
            EntregaCompletada::class => 'onEntregaCompletada',
            EntregaFallida::class => 'onEntregaFallida',
            DevolucionProducto::class => 'onDevolucionProducto',
        ];
    }
}
