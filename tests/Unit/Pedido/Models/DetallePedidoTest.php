<?php

namespace Tests\Unit\Pedido\Models;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;

it('table name', function () {
        $this->assertEquals('detalle_pedido', (new DetallePedido())->getTable());
    });

    it('primary key', function () {
        $this->assertEquals('id_detalle', (new DetallePedido())->getKeyName());
    });

    it('fillable', function () {
        $fillable = (new DetallePedido())->getFillable();
        $this->assertContains('id_pedido', $fillable);
        $this->assertContains('id_producto', $fillable);
        $this->assertContains('cantidad_solicitada', $fillable);
        $this->assertContains('precio_unitario', $fillable);
        $this->assertContains('subtotal', $fillable);
    });

    it('belongs to pedido', function () {
        $relation = (new DetallePedido())->pedido();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('belongs to producto', function () {
        $relation = (new DetallePedido())->producto();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });
