<?php

namespace Tests\Unit\Catalogo\Models;

use App\Models\ContactoFarmacia;
use App\Models\Farmacia;
use App\Models\Pedido;

it('table name', function () {
        $this->assertEquals('farmacias', (new Farmacia())->getTable());
    });

    it('primary key', function () {
        $this->assertEquals('id_farmacia', (new Farmacia())->getKeyName());
    });

    it('fillable', function () {
        $fillable = (new Farmacia())->getFillable();
        $this->assertContains('nombre', $fillable);
        $this->assertContains('direccion', $fillable);
        $this->assertContains('latitud', $fillable);
        $this->assertContains('longitud', $fillable);
    });

    it('has many contactos', function () {
        $relation = (new Farmacia())->contactos();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('has many pedidos', function () {
        $relation = (new Farmacia())->pedidos();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });
