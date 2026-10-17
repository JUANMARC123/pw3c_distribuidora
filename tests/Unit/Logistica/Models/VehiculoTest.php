<?php

namespace Tests\Unit\Logistica\Models;

use App\Models\AsignacionVehiculoRepartidor;
use App\Models\HojaRuta;
use App\Models\Vehiculo;

it('table name', function () {
        $this->assertEquals('vehiculos', (new Vehiculo())->getTable());
    });

    it('primary key', function () {
        $this->assertEquals('id_vehiculo', (new Vehiculo())->getKeyName());
    });

    it('fillable', function () {
        $fillable = (new Vehiculo())->getFillable();
        $this->assertContains('placa', $fillable);
        $this->assertContains('marca', $fillable);
        $this->assertContains('modelo', $fillable);
        $this->assertContains('estado', $fillable);
    });

    it('has many asignaciones', function () {
        $relation = (new Vehiculo())->asignaciones();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('has many hojas ruta', function () {
        $relation = (new Vehiculo())->hojasRuta();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });
