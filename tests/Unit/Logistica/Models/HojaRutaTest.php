<?php

namespace Tests\Unit\Logistica\Models;

use App\Models\CajaRepartidor;
use App\Models\HojaRuta;
use App\Models\HojaRutaDetalle;
use App\Models\Repartidor;
use App\Models\Vehiculo;

it('table name', function () {
        $this->assertEquals('hojas_ruta', (new HojaRuta())->getTable());
    });

    it('primary key', function () {
        $this->assertEquals('id_hoja_ruta', (new HojaRuta())->getKeyName());
    });

    it('fillable', function () {
        $fillable = (new HojaRuta())->getFillable();
        $this->assertContains('fecha', $fillable);
        $this->assertContains('id_repartidor', $fillable);
        $this->assertContains('id_vehiculo', $fillable);
        $this->assertContains('estado_ruta', $fillable);
    });

    it('belongs to repartidor', function () {
        $relation = (new HojaRuta())->repartidor();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('belongs to vehiculo', function () {
        $relation = (new HojaRuta())->vehiculo();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('has many detalles ordenados', function () {
        $relation = (new HojaRuta())->detalles();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('has one caja', function () {
        $relation = (new HojaRuta())->caja();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
    });
