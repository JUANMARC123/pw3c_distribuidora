<?php

namespace Tests\Unit\Auth\Models;

use App\Models\Pedido;
use App\Models\Repartidor;
use App\Models\Usuario;

it('table name', function () {
        $this->assertEquals('usuarios', (new Usuario())->getTable());
    });

    it('primary key', function () {
        $this->assertEquals('id_usuario', (new Usuario())->getKeyName());
    });

    it('fillable', function () {
        $fillable = (new Usuario())->getFillable();
        $this->assertContains('nombre', $fillable);
        $this->assertContains('apellido', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password_hash', $fillable);
        $this->assertContains('estado', $fillable);
    });

    it('hidden attributes', function () {
        $hidden = (new Usuario())->getHidden();
        $this->assertContains('password_hash', $hidden);
        $this->assertContains('remember_token', $hidden);
    });

    it('get auth password returns password hash', function () {
        $usuario = Usuario::factory()->create([
            'password_hash' => bcrypt('secret123'),
        ]);
        $this->assertEquals($usuario->password_hash, $usuario->getAuthPassword());
    });

    it('has api tokens trait', function () {
        $traits = class_uses(Usuario::class);
        $this->assertArrayHasKey(\Laravel\Sanctum\HasApiTokens::class, $traits);
    });

    it('has roles trait', function () {
        $traits = class_uses(Usuario::class);
        $this->assertArrayHasKey(\Spatie\Permission\Traits\HasRoles::class, $traits);
    });

    it('has one repartidor', function () {
        $relation = (new Usuario())->repartidor();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
    });

    it('has many pedidos', function () {
        $relation = (new Usuario())->pedidos();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    });

    it('create and assign role', function () {
        $usuario = Usuario::factory()->create();
        $usuario->assignRole('Administrador');
        $this->assertTrue($usuario->hasRole('Administrador'));
    });
