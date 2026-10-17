<?php

namespace Tests\Unit\Catalogo\Models;

use App\Models\Farmacia;
use App\Models\ContactoFarmacia;

it('table name', function () {
        $this->assertEquals('contactos_farmacia', (new ContactoFarmacia())->getTable());
    });

    it('primary key', function () {
        $this->assertEquals('id_contacto', (new ContactoFarmacia())->getKeyName());
    });

    it('fillable', function () {
        $fillable = (new ContactoFarmacia())->getFillable();
        $this->assertContains('id_farmacia', $fillable);
        $this->assertContains('nombre_contacto', $fillable);
        $this->assertContains('telefono', $fillable);
    });

    it('belongs to farmacia', function () {
        $relation = (new ContactoFarmacia())->farmacia();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals('id_farmacia', $relation->getForeignKeyName());
    });
