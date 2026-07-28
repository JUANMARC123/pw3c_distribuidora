<?php

namespace Tests\Unit\Auth\Requests;

use App\Http\Requests\LoginRequest;

$request = new LoginRequest();

    it('authorize devuelve true', function () use ($request) {
        $this->assertTrue($request->authorize());
    });

    it('rules contiene campos requeridos', function () use ($request) {
        $rules = $request->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
    });

    it('email es required y email', function () use ($request) {
        $rules = $request->rules();

        $this->assertStringContainsString('required', $rules['email']);
        $this->assertStringContainsString('email', $rules['email']);
    });

    it('password es required string y min 6', function () use ($request) {
        $rules = $request->rules();

        $this->assertStringContainsString('required', $rules['password']);
        $this->assertStringContainsString('string', $rules['password']);
        $this->assertStringContainsString('min:6', $rules['password']);
    });
