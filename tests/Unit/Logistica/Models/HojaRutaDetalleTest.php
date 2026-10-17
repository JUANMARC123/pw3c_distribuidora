<?php

namespace Tests\Unit\Logistica\Models;

use App\Models\Despacho;
use App\Models\HojaRuta;
use App\Models\HojaRutaDetalle;
use App\Models\Pedido;

it('table name', function () {
        $this->assertEquals('hoja_ruta_detalles', (new HojaRutaDetalle())->getTable());
    });

    it('primary key', function () {
        $this->assertEquals('id_detalle', (new HojaRutaDetalle())->getKeyName());
    });

    it('fillable', function () {
        $fillable = (new HojaRutaDetalle())->getFillable();
        $this->assertContains('id_hoja_ruta', $fillable);
        $this->assertContains('id_pedido', $fillable);
        $this->assertContains('orden_visita', $fillable);
    });

    it('belongs to hoja ruta', function () {
        $relation = (new HojaRutaDetalle())->hojaRuta();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('belongs to pedido', function () {
        $relation = (new HojaRutaDetalle())->pedido();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('has many despachos', function () {
        $relation = (new HojaRutaDetalle())->despachos();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });
