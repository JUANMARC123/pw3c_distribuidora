<?php

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;

it('table name', function () {
        // Preparar & Actuar
        $this->assertEquals('detalle_pedido', (new DetallePedido())->getTable());
    });

    it('primary key', function () {
        // Preparar & Actuar
        $this->assertEquals('id_detalle', (new DetallePedido())->getKeyName());
    });

    it('fillable', function () {
        // Preparar & Actuar
        $fillable = (new DetallePedido())->getFillable();
        // Verificar
        $this->assertContains('id_pedido', $fillable);
        $this->assertContains('id_producto', $fillable);
        $this->assertContains('cantidad_solicitada', $fillable);
        $this->assertContains('precio_unitario', $fillable);
        $this->assertContains('subtotal', $fillable);
    });

    it('belongs to pedido', function () {
        // Preparar & Actuar
        $relation = (new DetallePedido())->pedido();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('belongs to producto', function () {
        // Preparar & Actuar
        $relation = (new DetallePedido())->producto();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });
