<?php

namespace Tests\Unit\Pedido\Models;

use App\Models\Despacho;
use App\Models\DetallePedido;
use App\Models\Farmacia;
use App\Models\HojaRutaDetalle;
use App\Models\Pedido;
use App\Models\Usuario;

it('table name', function () {
        $this->assertEquals('pedidos', (new Pedido())->getTable());
    });

    it('primary key', function () {
        $this->assertEquals('id_pedido', (new Pedido())->getKeyName());
    });

    it('fillable', function () {
        $fillable = (new Pedido())->getFillable();
        $this->assertContains('id_farmacia', $fillable);
        $this->assertContains('id_usuario', $fillable);
        $this->assertContains('estado', $fillable);
        $this->assertContains('total', $fillable);
    });

    it('belongs to farmacia', function () {
        $relation = (new Pedido())->farmacia();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('belongs to usuario', function () {
        $relation = (new Pedido())->usuario();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('has many detalles', function () {
        $relation = (new Pedido())->detalles();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('has one despacho', function () {
        $relation = (new Pedido())->despacho();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
    });

    it('has many hoja ruta detalles', function () {
        $relation = (new Pedido())->hojaRutaDetalles();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });
