<?php

use App\Models\CajaRepartidor;
use App\Models\GastoOperativo;

it('table name', function () {
        // Preparar & Actuar
        $this->assertEquals('gastos_operativos', (new GastoOperativo())->getTable());
    });

    it('primary key', function () {
        // Preparar & Actuar
        $this->assertEquals('id_gasto', (new GastoOperativo())->getKeyName());
    });

    it('fillable', function () {
        // Preparar & Actuar
        $fillable = (new GastoOperativo())->getFillable();
        // Verificar
        $this->assertContains('id_caja', $fillable);
        $this->assertContains('tipo_gasto', $fillable);
        $this->assertContains('monto', $fillable);
        $this->assertContains('fecha', $fillable);
    });

    it('belongs to caja', function () {
        // Preparar & Actuar
        $relation = (new GastoOperativo())->caja();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });
