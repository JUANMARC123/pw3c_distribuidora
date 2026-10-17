<?php

namespace Tests\Unit\Logistica\Models;

use App\Models\Repartidor;
use App\Models\Vehiculo;
use App\Models\AsignacionVehiculoRepartidor;

it('table name', function () {
        $this->assertEquals('asignaciones_vehiculo_repartidor', (new AsignacionVehiculoRepartidor())->getTable());
    });

    it('primary key', function () {
        $this->assertEquals('id_asignacion', (new AsignacionVehiculoRepartidor())->getKeyName());
    });

    it('fillable', function () {
        $fillable = (new AsignacionVehiculoRepartidor())->getFillable();
        $this->assertContains('id_repartidor', $fillable);
        $this->assertContains('id_vehiculo', $fillable);
        $this->assertContains('fecha_asignacion', $fillable);
        $this->assertContains('fecha_devolucion', $fillable);
    });

    it('casts', function () {
        $casts = (new AsignacionVehiculoRepartidor())->getCasts();
        $this->assertEquals('datetime', $casts['fecha_asignacion']);
        $this->assertEquals('datetime', $casts['fecha_devolucion']);
    });

    it('belongs to repartidor', function () {
        $relation = (new AsignacionVehiculoRepartidor())->repartidor();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals('id_repartidor', $relation->getForeignKeyName());
    });

    it('belongs to vehiculo', function () {
        $relation = (new AsignacionVehiculoRepartidor())->vehiculo();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals('id_vehiculo', $relation->getForeignKeyName());
    });
