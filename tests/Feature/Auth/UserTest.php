<?php

uses()->group('auth', 'user');

it('obtiene el usuario autenticado', function () {
    // Preparar
    $user = actingAsAdmin();

    // Actuar
    $response = $this->getJson('/api/auth/user');

    // Verificar
    $response->assertOk()
        ->assertJsonPath('data.user.email', $user->email);
});

it('incluye permisos del usuario', function () {
    // Preparar
    actingAsAdmin();

    // Actuar
    $response = $this->getJson('/api/auth/user');

    // Verificar
    $response->assertOk()
        ->assertJsonStructure(['data' => ['user', 'permisos']]);
});

it('requiere autenticacion', function () {
    // Preparar

    // Actuar
    $response = $this->getJson('/api/auth/user');

    // Verificar
    $response->assertStatus(401);
});
