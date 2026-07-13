<?php

uses()->group('auth', 'logout');

it('cierra sesion correctamente', function () {
    // Preparar
    actingAsAdmin();

    // Actuar
    $response = $this->postJson('/api/auth/logout');

    // Verificar
    $response->assertOk()
        ->assertJson(['success' => true]);
});

it('requiere autenticacion para logout', function () {
    // Preparar

    // Actuar
    $response = $this->postJson('/api/auth/logout');

    // Verificar
    $response->assertStatus(401);
});
