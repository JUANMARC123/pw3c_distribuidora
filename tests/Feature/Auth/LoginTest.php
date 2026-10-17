<?php

/**
 * BDD: docs/specs/auth.md -> Escenarios A1, A2, A3 (Login, credenciales incorrectas, usuario bloqueado)
 * @see \App\Http\Controllers\AuthController::login
 */

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses()->group('auth', 'login');

beforeEach(function () {
    $this->user = Usuario::factory()->create([
        'email' => 'test@example.com',
        'password_hash' => Hash::make('password123'),
    ]);
    $this->user->assignRole('Administrador');
});

it('loguea con credenciales correctas', function () {
    // Preparar

    // Actuar
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    // Verificar
    $response->assertOk()
        ->assertJsonStructure([
            'success', 'message', 'data' => ['user', 'token', 'permisos'],
        ]);
});

it('rechaza contraseña incorrecta', function () {
    // Preparar

    // Actuar
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    // Verificar
    $response->assertStatus(422);
});

it('rechaza email inexistente', function () {
    // Preparar

    // Actuar
    $response = $this->postJson('/api/auth/login', [
        'email' => 'noexiste@example.com',
        'password' => 'password123',
    ]);

    // Verificar
    $response->assertStatus(422);
});

it('rechaza login sin credenciales', function () {
    // Preparar

    // Actuar
    $response = $this->postJson('/api/auth/login', []);

    // Verificar
    $response->assertStatus(422);
});

it('bloquea usuario inactivo', function () {
    // Preparar
    $this->user->update(['estado' => 'bloqueado']);

    // Actuar
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    // Verificar
    $response->assertStatus(403);
});
