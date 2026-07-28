<?php

namespace Tests\Unit\Logistica\Services;

use App\Models\AsignacionVehiculoRepartidor;
use App\Models\Repartidor;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Services\Logistica\AsignacionVehiculoService;

beforeEach(function () {
    $this->service = new AsignacionVehiculoService();
    $usuario = Usuario::factory()->create();
    $this->repartidor = Repartidor::factory()->create(['id_usuario' => $usuario->id_usuario]);
    $this->vehiculo = Vehiculo::factory()->create();
    $this->vehiculo2 = Vehiculo::factory()->create();
});

it('asignar crea asignacion', function () {
        $asignacion = $this->service->asignar(
            $this->repartidor->id_repartidor,
            $this->vehiculo->id_vehiculo
        );

        $this->assertDatabaseHas('asignaciones_vehiculo_repartidor', [
            'id_asignacion' => $asignacion->id_asignacion,
            'id_repartidor' => $this->repartidor->id_repartidor,
            'id_vehiculo' => $this->vehiculo->id_vehiculo,
        ]);
        expect($asignacion->fecha_devolucion)->toBeNull();
    });

    it('asignar cierra asignacion anterior', function () {
        $this->service->asignar(
            $this->repartidor->id_repartidor,
            $this->vehiculo->id_vehiculo
        );

        $this->service->asignar(
            $this->repartidor->id_repartidor,
            $this->vehiculo2->id_vehiculo
        );

        $asignaciones = AsignacionVehiculoRepartidor::where('id_repartidor', $this->repartidor->id_repartidor)
            ->orderBy('id_asignacion')
            ->get();

        expect($asignaciones)->toHaveCount(2);
        expect($asignaciones[0]->fecha_devolucion)->not->toBeNull();
        expect($asignaciones[1]->fecha_devolucion)->toBeNull();
        expect($asignaciones[1]->id_vehiculo)->toEqual($this->vehiculo2->id_vehiculo);
    });

    it('liberar cierra asignacion activa', function () {
        $this->service->asignar(
            $this->repartidor->id_repartidor,
            $this->vehiculo->id_vehiculo
        );

        $this->service->liberar($this->repartidor->id_repartidor);

        $asignacion = AsignacionVehiculoRepartidor::where('id_repartidor', $this->repartidor->id_repartidor)
            ->first();

        expect($asignacion->fecha_devolucion)->not->toBeNull();
    });

    it('liberar no falla si no hay asignacion activa', function () {
        $this->service->liberar($this->repartidor->id_repartidor);

        expect(true)->toBeTrue();
    });
