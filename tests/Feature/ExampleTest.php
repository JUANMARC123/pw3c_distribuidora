<?php

/**
 * BDD: docs/specs/ (test base de que la app responde)
 */

test('the application returns a successful response', function () {
    // Preparar

    // Actuar
    $response = $this->get('/');

    // Verificar
    $response->assertStatus(200);
});
