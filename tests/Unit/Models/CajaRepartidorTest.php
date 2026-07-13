<?php

use App\Models\CajaRepartidor;
use App\Models\GastoOperativo;
use App\Models\HojaRuta;
use App\Models\Repartidor;

it('table name', function () {
        // Preparar & Actuar
        $this->assertEquals('caja_repartidor', (new CajaRepartidor())->getTable());
    });

    it('primary key', function () {
        // Preparar & Actuar
        $this->assertEquals('id_caja', (new CajaRepartidor())->getKeyName());
    });

    it('fillable', function () {
        // Preparar & Actuar
        $fillable = (new CajaRepartidor())->getFillable();
        // Verificar
        $this->assertContains('id_hoja_ruta', $fillable);
        $this->assertContains('id_repartidor', $fillable);
        $this->assertContains('monto_inicial', $fillable);
        $this->assertContains('estado_caja', $fillable);
    });

    it('belongs to hoja ruta', function () {
        // Preparar & Actuar
        $relation = (new CajaRepartidor())->hojaRuta();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('belongs to repartidor', function () {
        // Preparar & Actuar
        $relation = (new CajaRepartidor())->repartidor();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('has many gastos', function () {
        // Preparar & Actuar
        $relation = (new CajaRepartidor())->gastos();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });
