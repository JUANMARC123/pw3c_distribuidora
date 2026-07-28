<?php

namespace Tests\Unit\Despacho\Models;

use App\Models\Despacho;
use App\Models\Incidencia;

it('table name', function () {
        $this->assertEquals('incidencias', (new Incidencia())->getTable());
    });

    it('primary key', function () {
        $this->assertEquals('id_incidencia', (new Incidencia())->getKeyName());
    });

    it('fillable', function () {
        $fillable = (new Incidencia())->getFillable();
        $this->assertContains('id_despacho', $fillable);
        $this->assertContains('tipo_incidencia', $fillable);
        $this->assertContains('descripcion', $fillable);
    });

    it('belongs to despacho', function () {
        $relation = (new Incidencia())->despacho();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals('id_despacho', $relation->getForeignKeyName());
    });
