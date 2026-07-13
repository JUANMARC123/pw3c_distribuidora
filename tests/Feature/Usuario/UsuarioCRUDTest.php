<?php

use App\Models\Usuario;

uses()->group('usuarios', 'crud');

beforeEach(fn () => actingAsAdmin());

it('lista usuarios paginados', function () {
    // Preparar
    Usuario::factory()->count(5)->create();

    // Actuar
    $response = $this->getJson('/api/usuarios');

    // Verificar
    $response->assertOk()
        ->assertJsonStructure(['success', 'data', 'meta']);
});

it('crea un usuario', function () {
    // Preparar

    // Actuar
    $response = $this->postJson('/api/usuarios', [
        'nombre' => 'Nuevo',
        'apellido' => 'Usuario',
        'email' => 'nuevo@example.com',
        'password' => 'password123',
        'telefono' => '71111111',
        'estado' => 'activo',
    ]);

    // Verificar
    $response->assertCreated()
        ->assertJsonPath('data.email', 'nuevo@example.com');
});

it('muestra un usuario', function () {
    // Preparar
    $user = Usuario::factory()->create();

    // Actuar
    $response = $this->getJson("/api/usuarios/{$user->id_usuario}");

    // Verificar
    $response->assertOk()
        ->assertJsonPath('data.id_usuario', $user->id_usuario);
});

it('actualiza un usuario', function () {
    // Preparar
    $user = Usuario::factory()->create();

    // Actuar
    $response = $this->putJson("/api/usuarios/{$user->id_usuario}", [
        'nombre' => 'Actualizado',
    ]);

    // Verificar
    $response->assertOk();
    expect(Usuario::find($user->id_usuario)->nombre)->toBe('Actualizado');
});

it('elimina un usuario', function () {
    // Preparar
    $user = Usuario::factory()->create();

    // Actuar
    $response = $this->deleteJson("/api/usuarios/{$user->id_usuario}");

    // Verificar
    $response->assertOk();
    expect(Usuario::find($user->id_usuario))->toBeNull();
});

it('bloquea un usuario', function () {
    // Preparar
    $user = Usuario::factory()->create();

    // Actuar
    $response = $this->putJson("/api/usuarios/{$user->id_usuario}/bloquear");

    // Verificar
    $response->assertOk();
    expect(Usuario::find($user->id_usuario)->estado)->toBe('bloqueado');
});
