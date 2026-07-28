<?php

namespace Tests\Unit\Logistica\Listeners;

use App\Events\DevolucionProducto;
use App\Events\EntregaCompletada;
use App\Events\EntregaFallida;
use App\Listeners\RegistrarLlegadaParadaListener;
use App\Models\Farmacia;
use App\Models\HojaRuta;
use App\Models\HojaRutaDetalle;
use App\Models\Pedido;
use App\Models\Repartidor;
use App\Models\Usuario;
use App\Models\Vehiculo;

beforeEach(function () {
    $this->listener = new RegistrarLlegadaParadaListener();
});

function crearDetalle(): HojaRutaDetalle
{
    $farmacia = Farmacia::factory()->create();
    $usuario = Usuario::factory()->create();
    $pedido = Pedido::factory()->create([
        'id_farmacia' => $farmacia->id_farmacia,
        'id_usuario' => $usuario->id_usuario,
    ]);
    $repartidor = Repartidor::factory()->create(['id_usuario' => $usuario->id_usuario]);
    $vehiculo = Vehiculo::factory()->create();
    $hojaRuta = HojaRuta::factory()->create([
        'id_repartidor' => $repartidor->id_repartidor,
        'id_vehiculo' => $vehiculo->id_vehiculo,
    ]);

    return HojaRutaDetalle::factory()->create([
        'id_hoja_ruta' => $hojaRuta->id_hoja_ruta,
        'id_pedido' => $pedido->id_pedido,
        'hora_llegada' => null,
    ]);
}

it('handle entrega registra hora llegada si no existe', function () {
        $detalle = crearDetalle();
        $event = new EntregaCompletada($detalle, 1, $detalle->pedido->id_pedido);

        $this->listener->handleEntrega($event);

        $this->assertNotNull($detalle->fresh()->hora_llegada);
    });

    it('handle fallida registra hora llegada si no existe', function () {
        $detalle = crearDetalle();
        $event = new EntregaFallida($detalle, 1, $detalle->pedido->id_pedido, 'Ausente');

        $this->listener->handleFallida($event);

        $this->assertNotNull($detalle->fresh()->hora_llegada);
    });

    it('handle devolucion registra hora llegada si no existe', function () {
        $detalle = crearDetalle();
        $event = new DevolucionProducto($detalle, 1, $detalle->pedido->id_pedido);

        $this->listener->handleDevolucion($event);

        $this->assertNotNull($detalle->fresh()->hora_llegada);
    });

    it('no sobrescribe hora llegada existente', function () {
        $detalle = crearDetalle();
        $detalle->update(['hora_llegada' => '10:30:00']);
        $horaOriginal = $detalle->fresh()->hora_llegada;

        $event = new EntregaCompletada($detalle, 1, $detalle->pedido->id_pedido);

        $this->listener->handleEntrega($event);

        $this->assertEquals($horaOriginal, $detalle->fresh()->hora_llegada);
    });
