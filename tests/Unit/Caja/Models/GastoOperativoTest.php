<?php

namespace Tests\Unit\Caja\Models;

use App\Models\CajaRepartidor;
use App\Models\GastoOperativo;

it('table name', function () {
        $this->assertEquals('gastos_operativos', (new GastoOperativo())->getTable());
    });

    it('primary key', function () {
        $this->assertEquals('id_gasto', (new GastoOperativo())->getKeyName());
    });

    it('fillable', function () {
        $fillable = (new GastoOperativo())->getFillable();
        $this->assertContains('id_caja', $fillable);
        $this->assertContains('tipo_gasto', $fillable);
        $this->assertContains('monto', $fillable);
        $this->assertContains('fecha', $fillable);
    });

    it('belongs to caja', function () {
        $relation = (new GastoOperativo())->caja();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });
