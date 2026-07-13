<?php

use App\Models\Despacho;
use App\Models\DetallePedido;
use App\Models\Farmacia;
use App\Models\HojaRutaDetalle;
use App\Models\Pedido;
use App\Models\Usuario;

it('table name', function () {
        // Preparar & Actuar
        $this->assertEquals('pedidos', (new Pedido())->getTable());
    });

    it('primary key', function () {
        // Preparar & Actuar
        $this->assertEquals('id_pedido', (new Pedido())->getKeyName());
    });

    it('fillable', function () {
        // Preparar & Actuar
        $fillable = (new Pedido())->getFillable();

        // Verificar
        $this->assertContains('id_farmacia', $fillable);
        $this->assertContains('id_usuario', $fillable);
        $this->assertContains('estado', $fillable);
        $this->assertContains('total', $fillable);
    });

    it('belongs to farmacia', function () {
        // Preparar & Actuar
        $relation = (new Pedido())->farmacia();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('belongs to usuario', function () {
        // Preparar & Actuar
        $relation = (new Pedido())->usuario();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('has many detalles', function () {
        // Preparar & Actuar
        $relation = (new Pedido())->detalles();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('has one despacho', function () {
        // Preparar & Actuar
        $relation = (new Pedido())->despacho();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
    });

    it('has many hoja ruta detalles', function () {
        // Preparar & Actuar
        $relation = (new Pedido())->hojaRutaDetalles();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });
