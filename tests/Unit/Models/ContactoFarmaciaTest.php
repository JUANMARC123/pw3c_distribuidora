<?php

use App\Models\Farmacia;
use App\Models\ContactoFarmacia;

it('table name', function () {
        // Preparar & Actuar
        $this->assertEquals('contactos_farmacia', (new ContactoFarmacia())->getTable());
    });

    it('primary key', function () {
        // Preparar & Actuar
        $this->assertEquals('id_contacto', (new ContactoFarmacia())->getKeyName());
    });

    it('fillable', function () {
        // Preparar & Actuar
        $fillable = (new ContactoFarmacia())->getFillable();
        // Verificar
        $this->assertContains('id_farmacia', $fillable);
        $this->assertContains('nombre_contacto', $fillable);
        $this->assertContains('telefono', $fillable);
    });

    it('belongs to farmacia', function () {
        // Preparar & Actuar
        $relation = (new ContactoFarmacia())->farmacia();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals('id_farmacia', $relation->getForeignKeyName());
    });
