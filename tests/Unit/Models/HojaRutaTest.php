<?php

use App\Models\CajaRepartidor;
use App\Models\HojaRuta;
use App\Models\HojaRutaDetalle;
use App\Models\Repartidor;
use App\Models\Vehiculo;

it('table name', function () {
        // Preparar & Actuar
        $this->assertEquals('hojas_ruta', (new HojaRuta())->getTable());
    });

    it('primary key', function () {
        // Preparar & Actuar
        $this->assertEquals('id_hoja_ruta', (new HojaRuta())->getKeyName());
    });

    it('fillable', function () {
        // Preparar & Actuar
        $fillable = (new HojaRuta())->getFillable();

        // Verificar
        $this->assertContains('fecha', $fillable);
        $this->assertContains('id_repartidor', $fillable);
        $this->assertContains('id_vehiculo', $fillable);
        $this->assertContains('estado_ruta', $fillable);
    });

    it('belongs to repartidor', function () {
        // Preparar & Actuar
        $relation = (new HojaRuta())->repartidor();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('belongs to vehiculo', function () {
        // Preparar & Actuar
        $relation = (new HojaRuta())->vehiculo();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('has many detalles ordenados', function () {
        // Preparar & Actuar
        $relation = (new HojaRuta())->detalles();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('has one caja', function () {
        // Preparar & Actuar
        $relation = (new HojaRuta())->caja();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
    });
