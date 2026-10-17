<?php

namespace App\Events;

use App\Models\HojaRuta;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RutaIniciada
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public HojaRuta $hojaRuta;
    public array $pedidoIds;
    public int $idVehiculo;

    public function __construct(HojaRuta $hojaRuta, array $pedidoIds, int $idVehiculo)
    {
        $this->hojaRuta = $hojaRuta;
        $this->pedidoIds = $pedidoIds;
        $this->idVehiculo = $idVehiculo;
    }
}
