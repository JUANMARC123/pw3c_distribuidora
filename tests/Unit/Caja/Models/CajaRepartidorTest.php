<?php

namespace Tests\Unit\Caja\Models;

use App\Models\CajaRepartidor;
use App\Models\GastoOperativo;
use App\Models\HojaRuta;
use App\Models\Repartidor;

it('table name', function () {
        $this->assertEquals('caja_repartidor', (new CajaRepartidor())->getTable());
    });

    it('primary key', function () {
        $this->assertEquals('id_caja', (new CajaRepartidor())->getKeyName());
    });

    it('fillable', function () {
        $fillable = (new CajaRepartidor())->getFillable();
        $this->assertContains('id_repartidor', $fillable);
        $this->assertContains('monto_inicial', $fillable);
        $this->assertContains('estado_caja', $fillable);
    });

    it('has many hojas ruta', function () {
        $relation = (new CajaRepartidor())->hojasRuta();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('belongs to repartidor', function () {
        $relation = (new CajaRepartidor())->repartidor();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('has many gastos', function () {
        $relation = (new CajaRepartidor())->gastos();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });
