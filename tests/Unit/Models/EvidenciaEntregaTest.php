<?php

use App\Models\Despacho;
use App\Models\EvidenciaEntrega;

it('table name', function () {
        // Preparar & Actuar
        $this->assertEquals('evidencias_entrega', (new EvidenciaEntrega())->getTable());
    });

    it('primary key', function () {
        // Preparar & Actuar
        $this->assertEquals('id_evidencia', (new EvidenciaEntrega())->getKeyName());
    });

    it('fillable', function () {
        // Preparar & Actuar
        $fillable = (new EvidenciaEntrega())->getFillable();
        // Verificar
        $this->assertContains('id_despacho', $fillable);
        $this->assertContains('tipo_evidencia', $fillable);
        $this->assertContains('archivo', $fillable);
    });

    it('belongs to despacho', function () {
        // Preparar & Actuar
        $relation = (new EvidenciaEntrega())->despacho();
        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });
