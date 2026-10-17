<?php

namespace App\Events;

use App\Models\HojaRutaDetalle;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EntregaFallida
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public HojaRutaDetalle $detalle;
    public int $idDespacho;
    public int $idPedido;
    public string $motivo;

    public function __construct(HojaRutaDetalle $detalle, int $idDespacho, int $idPedido, string $motivo = '')
    {
        $this->detalle = $detalle;
        $this->idDespacho = $idDespacho;
        $this->idPedido = $idPedido;
        $this->motivo = $motivo;
    }
}
