<?php

use App\Models\Despacho;
use App\Models\Incidencia;

it('table name', function () {
        // Preparar & Actuar
        $this->assertEquals('incidencias', (new Incidencia())->getTable());
    });

    it('primary key', function () {
        // Preparar & Actuar
        $this->assertEquals('id_incidencia', (new Incidencia())->getKeyName());
    });

    it('fillable', function () {
        // Preparar & Actuar
        $fillable = (new Incidencia())->getFillable();

        // Verificar
        $this->assertContains('id_despacho', $fillable);
        $this->assertContains('tipo_incidencia', $fillable);
        $this->assertContains('descripcion', $fillable);
    });

    it('belongs to despacho', function () {
        // Preparar & Actuar
        $relation = (new Incidencia())->despacho();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals('id_despacho', $relation->getForeignKeyName());
    });
