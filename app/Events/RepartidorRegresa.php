<?php

namespace App\Events;

use App\Models\HojaRuta;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RepartidorRegresa
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public HojaRuta $hojaRuta;
    public int $idRepartidor;
    public int $idVehiculo;

    public function __construct(HojaRuta $hojaRuta, int $idRepartidor, int $idVehiculo)
    {
        $this->hojaRuta = $hojaRuta;
        $this->idRepartidor = $idRepartidor;
        $this->idVehiculo = $idVehiculo;
    }
}
