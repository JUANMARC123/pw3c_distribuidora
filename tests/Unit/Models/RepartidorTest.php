<?php

use App\Models\AsignacionVehiculoRepartidor;
use App\Models\CajaRepartidor;
use App\Models\HojaRuta;
use App\Models\Repartidor;
use App\Models\Usuario;

it('table name', function () {
        // Preparar & Actuar
        $this->assertEquals('repartidores', (new Repartidor())->getTable());
    });

    it('primary key', function () {
        // Preparar & Actuar
        $this->assertEquals('id_repartidor', (new Repartidor())->getKeyName());
    });

    it('fillable', function () {
        // Preparar & Actuar
        $fillable = (new Repartidor())->getFillable();

        // Verificar
        $this->assertContains('id_usuario', $fillable);
        $this->assertContains('ci', $fillable);
        $this->assertContains('categoria_licencia', $fillable);
        $this->assertContains('estado', $fillable);
    });

    it('belongs to usuario', function () {
        // Preparar & Actuar
        $relation = (new Repartidor())->usuario();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
    });

    it('has many asignaciones vehiculo', function () {
        // Preparar & Actuar
        $relation = (new Repartidor())->asignacionesVehiculo();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('has one asignacion activa', function () {
        // Preparar & Actuar
        $relation = (new Repartidor())->asignacionActiva();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
    });

    it('has many hojas ruta', function () {
        // Preparar & Actuar
        $relation = (new Repartidor())->hojasRuta();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('has many cajas', function () {
        // Preparar & Actuar
        $relation = (new Repartidor())->cajas();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('asignacion activa scoped to null fecha devolucion', function () {
        // Preparar
        $usuario = Usuario::factory()->create();
        $repartidor = Repartidor::factory()->create(['id_usuario' => $usuario->id_usuario]);

        $asignacionVieja = AsignacionVehiculoRepartidor::factory()->create([
            'id_repartidor' => $repartidor->id_repartidor,
            'fecha_devolucion' => now()->subDay(),
        ]);
        $asignacionActiva = AsignacionVehiculoRepartidor::factory()->create([
            'id_repartidor' => $repartidor->id_repartidor,
            'fecha_devolucion' => null,
        ]);

        // Actuar
        $result = $repartidor->asignacionActiva;

        // Verificar
        $this->assertNotNull($result);
        $this->assertEquals($asignacionActiva->id_asignacion, $result->id_asignacion);
    });
