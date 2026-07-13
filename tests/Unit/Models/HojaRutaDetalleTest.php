<?php

use App\Models\Despacho;
use App\Models\HojaRuta;
use App\Models\HojaRutaDetalle;
use App\Models\Pedido;

it('table name', function () {
        // Preparar & Actuar
        $this->assertEquals('hoja_ruta_detalles', (new HojaRutaDetalle())->getTable());
    });

    it('primary key', function () {
        // Preparar & Actuar
        $this->assertEquals('id_detalle', (new HojaRutaDetalle())->getKeyName());
    });

    it('fillable', function () {
        // Preparar & Actuar
        $fillable = (new HojaRutaDetalle())->getFillable();
        // Verificar
        $this->assertContains('id_hoja_ruta', $fillable);
        $this->assertContains('id_pedido', $fillable);
        $this->assertContains('orden_visita', $fillable);
    });

    it('belongs to hoja ruta', function () {
        // Preparar & Actuar
        $relation = (new HojaRutaDetalle())->hojaRuta();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('belongs to pedido', function () {
        // Preparar & Actuar
        $relation = (new HojaRutaDetalle())->pedido();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('has many despachos', function () {
        // Preparar & Actuar
        $relation = (new HojaRutaDetalle())->despachos();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });
