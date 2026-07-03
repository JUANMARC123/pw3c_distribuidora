<?php

namespace App\Events;

use App\Models\Logistica\ControlRuta;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RutaIniciada
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ControlRuta $controlRuta;
    public array $pedidoIds;
    public int $vehiculoId;

    public function __construct(ControlRuta $controlRuta, array $pedidoIds, int $vehiculoId)
    {
        $this->controlRuta = $controlRuta;
        $this->pedidoIds = $pedidoIds;
        $this->vehiculoId = $vehiculoId;
    }
}
