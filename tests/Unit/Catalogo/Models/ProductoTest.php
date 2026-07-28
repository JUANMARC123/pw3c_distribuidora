<?php

namespace Tests\Unit\Catalogo\Models;

use App\Models\DetallePedido;
use App\Models\Producto;

it('table name', function () {
        $this->assertEquals('productos', (new Producto())->getTable());
    });

    it('primary key', function () {
        $this->assertEquals('id_producto', (new Producto())->getKeyName());
    });

    it('fillable', function () {
        $fillable = (new Producto())->getFillable();
        $this->assertContains('nombre', $fillable);
        $this->assertContains('precio_unitario', $fillable);
        $this->assertContains('unidad_medida', $fillable);
    });

    it('has many detalle pedidos', function () {
        $relation = (new Producto())->detallePedidos();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });
