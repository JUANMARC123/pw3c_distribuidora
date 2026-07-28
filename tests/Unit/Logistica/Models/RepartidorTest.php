<?php

namespace Tests\Unit\Logistica\Models;

use App\Models\AsignacionVehiculoRepartidor;
use App\Models\CajaRepartidor;
use App\Models\HojaRuta;
use App\Models\Repartidor;
use App\Models\Usuario;

it('table name', function () {
        $this->assertEquals('repartidores', (new Repartidor())->getTable());
    });

    it('primary key', function () {
        $this->assertEquals('id_repartidor', (new Repartidor())->getKeyName());
    });

    it('fillable', function () {
        $fillable = (new Repartidor())->getFillable();
        $this->assertContains('id_usuario', $fillable);
        $this->assertContains('ci', $fillable);
        $this->assertContains('categoria_licencia', $fillable);
        $this->assertContains('estado', $fillable);
    });

    it('belongs to usuario', function () {
        $relation = (new Repartidor())->usuario();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('has many asignaciones vehiculo', function () {
        $relation = (new Repartidor())->asignacionesVehiculo();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('has one asignacion activa', function () {
        $relation = (new Repartidor())->asignacionActiva();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
    });

    it('has many hojas ruta', function () {
        $relation = (new Repartidor())->hojasRuta();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('has many cajas', function () {
        $relation = (new Repartidor())->cajas();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('asignacion activa scoped to null fecha devolucion', function () {
        $usuario = Usuario::factory()->create();
        $repartidor = Repartidor::factory()->create(['id_usuario' => $usuario->id_usuario]);

        $asignacionVieja = AsignacionVehiculoRepartidor::factory()->create([
            'id_repartidor' => $repartidor->id_repartidor,
            'fecha_devolucion' => now()->subDay(),
        ]);
        $asignacionActiva = AsignacionVehiculoRepartidor::factory()->create([
            'id_repartidor' => $repartidor->id_repartidor,
            'fecha_devolucion' => null,
        ]);

        $result = $repartidor->asignacionActiva;

        $this->assertNotNull($result);
        $this->assertEquals($asignacionActiva->id_asignacion, $result->id_asignacion);
    });
