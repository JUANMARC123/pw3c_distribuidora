<?php

namespace App\Events;

use App\Models\Logistica\ControlRutaParada;
use Illuminate\Foundation\Events\Dispatchable;

class EntregaCompletada
{
    use Dispatchable;

    public ControlRutaParada $parada;
    public int $idDespacho;
    public int $idPedido;

    public function __construct(ControlRutaParada $parada, int $idDespacho, int $idPedido)
    {
        $this->parada = $parada;
        $this->idDespacho = $idDespacho;
        $this->idPedido = $idPedido;
    }
}
