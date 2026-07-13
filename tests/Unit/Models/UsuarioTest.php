<?php

use App\Models\Pedido;
use App\Models\Repartidor;
use App\Models\Usuario;

it('table name', function () {
        // Preparar & Actuar
        $this->assertEquals('usuarios', (new Usuario())->getTable());
    });

    it('primary key', function () {
        // Preparar & Actuar
        $this->assertEquals('id_usuario', (new Usuario())->getKeyName());
    });

    it('fillable', function () {
        // Preparar & Actuar
        $fillable = (new Usuario())->getFillable();

        // Verificar
        $this->assertContains('nombre', $fillable);
        $this->assertContains('apellido', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password_hash', $fillable);
        $this->assertContains('estado', $fillable);
    });

    it('hidden attributes', function () {
        // Preparar & Actuar
        $hidden = (new Usuario())->getHidden();

        // Verificar
        $this->assertContains('password_hash', $hidden);
        $this->assertContains('remember_token', $hidden);
    });

    it('get auth password returns password hash', function () {
        // Preparar
        $usuario = Usuario::factory()->create([
            'password_hash' => bcrypt('secret123'),
        ]);

        // Actuar & Verificar
        $this->assertEquals($usuario->password_hash, $usuario->getAuthPassword());
    });

    it('has api tokens trait', function () {
        // Preparar & Actuar
        $traits = class_uses(Usuario::class);

        // Verificar
        $this->assertArrayHasKey(\Laravel\Sanctum\HasApiTokens::class, $traits);
    });

    it('has roles trait', function () {
        // Preparar & Actuar
        $traits = class_uses(Usuario::class);

        // Verificar
        $this->assertArrayHasKey(\Spatie\Permission\Traits\HasRoles::class, $traits);
    });

    it('has one repartidor', function () {
        // Preparar & Actuar
        $relation = (new Usuario())->repartidor();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
    });

    it('has many pedidos', function () {
        // Preparar & Actuar
        $relation = (new Usuario())->pedidos();

        // Verificar
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('create and assign role', function () {
        // Preparar
        $usuario = Usuario::factory()->create();

        // Actuar
        $usuario->assignRole('Administrador');

        // Verificar
        $this->assertTrue($usuario->hasRole('Administrador'));
    });
