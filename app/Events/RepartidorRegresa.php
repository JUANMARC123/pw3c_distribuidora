<?php

namespace App\Events;

use App\Models\Logistica\ControlRuta;
use Illuminate\Foundation\Events\Dispatchable;

class RepartidorRegresa
{
    use Dispatchable;

    public ControlRuta $controlRuta;
    public int $idRepartidor;
    public int $idVehiculo;

    public function __construct(ControlRuta $controlRuta, int $idRepartidor, int $idVehiculo)
    {
        $this->controlRuta = $controlRuta;
        $this->idRepartidor = $idRepartidor;
        $this->idVehiculo = $idVehiculo;
    }
}
