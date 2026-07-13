<?php

use App\Models\ContactoFarmacia;
use App\Models\Farmacia;
use App\Models\Pedido;

it('table name', function () {
        // Preparar & Actuar
        $this->assertEquals('farmacias', (new Farmacia())->getTable());
    });

    it('primary key', function () {
        // Preparar & Actuar
        $this->assertEquals('id_farmacia', (new Farmacia())->getKeyName());
    });

    it('fillable', function () {
        // Preparar & Actuar
        $fillable = (new Farmacia())->getFillable();
        // Verificar
        $this->assertContains('nombre', $fillable);
        $this->assertContains('direccion', $fillable);
        $this->assertContains('latitud', $fillable);
        $this->assertContains('longitud', $fillable);
    });

    it('has many contactos', function () {
        // Preparar & Actuar
        $relation = (new Farmacia())->contactos();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('has many pedidos', function () {
        // Preparar & Actuar
        $relation = (new Farmacia())->pedidos();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });
