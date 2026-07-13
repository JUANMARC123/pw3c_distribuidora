<?php

uses()->group('auth', 'register');

it('registra un nuevo usuario', function () {
    // Preparar

    // Actuar
    $response = $this->postJson('/api/auth/register', [
        'nombre' => 'Juan',
        'apellido' => 'Perez',
        'email' => 'juan@example.com',
        'password' => 'password123',
        'telefono' => '70000000',
    ]);

    // Verificar
    $response->assertCreated()
        ->assertJsonStructure([
            'success', 'message', 'data' => ['user', 'token', 'permisos'],
        ]);
});

it('rechaza email duplicado', function () {
    // Preparar
    \App\Models\Usuario::factory()->create(['email' => 'dup@example.com']);

    // Actuar
    $response = $this->postJson('/api/auth/register', [
        'nombre' => 'Test',
        'apellido' => 'User',
        'email' => 'dup@example.com',
        'password' => 'password123',
    ]);

    // Verificar
    $response->assertStatus(422);
});

it('requiere nombre, apellido, email y password', function () {
    // Preparar

    // Actuar
    $response = $this->postJson('/api/auth/register', []);

    // Verificar
    $response->assertStatus(422);
});

it('requiere password minimo 6 caracteres', function () {
    // Preparar

    // Actuar
    $response = $this->postJson('/api/auth/register', [
        'nombre' => 'Test',
        'apellido' => 'User',
        'email' => 'test@example.com',
        'password' => '12345',
    ]);

    // Verificar
    $response->assertStatus(422);
});
