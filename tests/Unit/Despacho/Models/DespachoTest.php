<?php

namespace Tests\Unit\Despacho\Models;

use App\Models\Despacho;
use App\Models\EvidenciaEntrega;
use App\Models\HojaRutaDetalle;
use App\Models\Incidencia;
use App\Models\Pedido;

it('table name', function () {
        $this->assertEquals('despachos', (new Despacho())->getTable());
    });

    it('primary key', function () {
        $this->assertEquals('id_despacho', (new Despacho())->getKeyName());
    });

    it('fillable', function () {
        $fillable = (new Despacho())->getFillable();
        $this->assertContains('id_pedido', $fillable);
        $this->assertContains('estado_despacho', $fillable);
        $this->assertContains('monto_recibido', $fillable);
    });

    it('belongs to pedido', function () {
        $relation = (new Despacho())->pedido();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('belongs to hoja ruta detalle', function () {
        $relation = (new Despacho())->hojaRutaDetalle();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('has many evidencias', function () {
        $relation = (new Despacho())->evidencias();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('has many incidencias', function () {
        $relation = (new Despacho())->incidencias();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });
