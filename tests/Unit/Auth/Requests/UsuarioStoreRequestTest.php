<?php

namespace Tests\Unit\Auth\Requests;

use App\Http\Requests\Seguridad\UsuarioStoreRequest;

$request = new UsuarioStoreRequest();

    it('authorize devuelve true', function () use ($request) {
        $this->assertTrue($request->authorize());
    });

    it('rules contiene todos los campos', function () use ($request) {
        $rules = $request->rules();

        $expected = ['nombre', 'apellido', 'email', 'password', 'telefono', 'id_estado_usuario', 'roles'];
        foreach ($expected as $field) {
            $this->assertArrayHasKey($field, $rules, "Falta campo: $field");
        }
    });

    it('telefono es required no nullable', function () use ($request) {
        $rules = $request->rules();

        $this->assertStringContainsString('required', $rules['telefono']);
        $this->assertStringNotContainsString('nullable', $rules['telefono']);
    });

    it('id estado usuario requiere integer y exists', function () use ($request) {
        $rules = $request->rules();

        $this->assertStringContainsString('exists:estados_usuario,id_estado_usuario', $rules['id_estado_usuario']);
    });

    it('roles es array opcional', function () use ($request) {
        $rules = $request->rules();

        $this->assertStringContainsString('sometimes', $rules['roles']);
        $this->assertStringContainsString('array', $rules['roles']);
    });

    it('roles items requieren integer y exists', function () use ($request) {
        $rules = $request->rules();

        $this->assertStringContainsString('exists:roles,id_rol', $rules['roles.*']);
    });
