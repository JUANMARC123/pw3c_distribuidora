<?php

use App\Models\AsignacionVehiculoRepartidor;
use App\Models\HojaRuta;
use App\Models\Vehiculo;

it('table name', function () {
        // Preparar & Actuar
        $this->assertEquals('vehiculos', (new Vehiculo())->getTable());
    });

    it('primary key', function () {
        // Preparar & Actuar
        $this->assertEquals('id_vehiculo', (new Vehiculo())->getKeyName());
    });

    it('fillable', function () {
        // Preparar & Actuar
        $fillable = (new Vehiculo())->getFillable();

        // Verificar
        $this->assertContains('placa', $fillable);
        $this->assertContains('marca', $fillable);
        $this->assertContains('modelo', $fillable);
        $this->assertContains('estado', $fillable);
    });

    it('has many asignaciones', function () {
        // Preparar & Actuar
        $relation = (new Vehiculo())->asignaciones();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('has many hojas ruta', function () {
        // Preparar & Actuar
        $relation = (new Vehiculo())->hojasRuta();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });
