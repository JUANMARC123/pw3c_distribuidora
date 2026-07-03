<?php

namespace App\Events;

use App\Models\Logistica\ControlRutaParada;
use Illuminate\Foundation\Events\Dispatchable;

class EntregaFallida
{
    use Dispatchable;

    public ControlRutaParada $parada;
    public int $idDespacho;
    public int $idPedido;
    public string $motivo;

    public function __construct(ControlRutaParada $parada, int $idDespacho, int $idPedido, string $motivo = '')
    {
        $this->parada = $parada;
        $this->idDespacho = $idDespacho;
        $this->idPedido = $idPedido;
        $this->motivo = $motivo;
    }
}
