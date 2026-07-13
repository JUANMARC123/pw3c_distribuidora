<?php

use App\Models\Repartidor;
use App\Models\Vehiculo;
use App\Models\AsignacionVehiculoRepartidor;

it('table name', function () {
        // Preparar & Actuar
        $this->assertEquals('asignaciones_vehiculo_repartidor', (new AsignacionVehiculoRepartidor())->getTable());
    });

    it('primary key', function () {
        // Preparar & Actuar
        $this->assertEquals('id_asignacion', (new AsignacionVehiculoRepartidor())->getKeyName());
    });

    it('fillable', function () {
        // Preparar & Actuar
        $fillable = (new AsignacionVehiculoRepartidor())->getFillable();
        // Verificar
        $this->assertContains('id_repartidor', $fillable);
        $this->assertContains('id_vehiculo', $fillable);
        $this->assertContains('fecha_asignacion', $fillable);
        $this->assertContains('fecha_devolucion', $fillable);
    });

    it('casts', function () {
        // Preparar & Actuar
        $casts = (new AsignacionVehiculoRepartidor())->getCasts();
        // Verificar
        $this->assertEquals('datetime', $casts['fecha_asignacion']);
        $this->assertEquals('datetime', $casts['fecha_devolucion']);
    });

    it('belongs to repartidor', function () {
        // Preparar & Actuar
        $relation = (new AsignacionVehiculoRepartidor())->repartidor();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals('id_repartidor', $relation->getForeignKeyName());
    });

    it('belongs to vehiculo', function () {
        // Preparar & Actuar
        $relation = (new AsignacionVehiculoRepartidor())->vehiculo();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals('id_vehiculo', $relation->getForeignKeyName());
    });
