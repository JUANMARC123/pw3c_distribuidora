<?php

namespace Tests\Unit\Despacho\Models;

use App\Models\Despacho;
use App\Models\EvidenciaEntrega;

it('table name', function () {
        $this->assertEquals('evidencias_entrega', (new EvidenciaEntrega())->getTable());
    });

    it('primary key', function () {
        $this->assertEquals('id_evidencia', (new EvidenciaEntrega())->getKeyName());
    });

    it('fillable', function () {
        $fillable = (new EvidenciaEntrega())->getFillable();
        $this->assertContains('id_despacho', $fillable);
        $this->assertContains('tipo_evidencia', $fillable);
        $this->assertContains('archivo', $fillable);
    });

    it('belongs to despacho', function () {
        $relation = (new EvidenciaEntrega())->despacho();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });
