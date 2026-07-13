<?php

test('the application returns a successful response', function () {
    // Preparar

    // Actuar
    $response = $this->get('/');

    // Verificar
    $response->assertStatus(200);
});
