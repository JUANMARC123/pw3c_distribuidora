<?php

namespace Tests\Unit\Auth\Requests;

use App\Http\Requests\RegisterRequest;

$request = new RegisterRequest();

    it('authorize devuelve true', function () use ($request) {
        $this->assertTrue($request->authorize());
    });

    it('rules contiene todos los campos', function () use ($request) {
        $rules = $request->rules();

        $this->assertArrayHasKey('nombre', $rules);
        $this->assertArrayHasKey('apellido', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertArrayHasKey('telefono', $rules);
    });

    it('nombre es required string max 100', function () use ($request) {
        $rules = $request->rules();

        $this->assertStringContainsString('required', $rules['nombre']);
        $this->assertStringContainsString('string', $rules['nombre']);
        $this->assertStringContainsString('max:100', $rules['nombre']);
    });

    it('email tiene unique', function () use ($request) {
        $rules = $request->rules();

        $this->assertStringContainsString('unique:usuarios,email', $rules['email']);
    });

    it('password es required string min 6', function () use ($request) {
        $rules = $request->rules();

        $this->assertStringContainsString('required', $rules['password']);
        $this->assertStringContainsString('min:6', $rules['password']);
    });

    it('telefono es nullable', function () use ($request) {
        $rules = $request->rules();

        $this->assertStringContainsString('nullable', $rules['telefono']);
    });
