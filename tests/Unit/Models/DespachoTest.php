<?php

use App\Models\Despacho;
use App\Models\EvidenciaEntrega;
use App\Models\HojaRutaDetalle;
use App\Models\Incidencia;
use App\Models\Pedido;

it('table name', function () {
        // Preparar & Actuar
        $this->assertEquals('despachos', (new Despacho())->getTable());
    });

    it('primary key', function () {
        // Preparar & Actuar
        $this->assertEquals('id_despacho', (new Despacho())->getKeyName());
    });

    it('fillable', function () {
        // Preparar & Actuar
        $fillable = (new Despacho())->getFillable();
        // Verificar
        $this->assertContains('id_pedido', $fillable);
        $this->assertContains('estado_despacho', $fillable);
        $this->assertContains('monto_recibido', $fillable);
    });

    it('belongs to pedido', function () {
        // Preparar & Actuar
        $relation = (new Despacho())->pedido();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('belongs to hoja ruta detalle', function () {
        // Preparar & Actuar
        $relation = (new Despacho())->hojaRutaDetalle();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('has many evidencias', function () {
        // Preparar & Actuar
        $relation = (new Despacho())->evidencias();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('has many incidencias', function () {
        // Preparar & Actuar
        $relation = (new Despacho())->incidencias();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });
