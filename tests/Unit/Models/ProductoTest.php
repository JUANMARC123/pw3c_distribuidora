<?php

use App\Models\DetallePedido;
use App\Models\Producto;

it('table name', function () {
        // Preparar & Actuar
        $this->assertEquals('productos', (new Producto())->getTable());
    });

    it('primary key', function () {
        // Preparar & Actuar
        $this->assertEquals('id_producto', (new Producto())->getKeyName());
    });

    it('fillable', function () {
        // Preparar & Actuar
        $fillable = (new Producto())->getFillable();

        // Verificar
        $this->assertContains('nombre', $fillable);
        $this->assertContains('precio_unitario', $fillable);
        $this->assertContains('unidad_medida', $fillable);
    });

    it('has many detalle pedidos', function () {
        // Preparar & Actuar
        $relation = (new Producto())->detallePedidos();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });
