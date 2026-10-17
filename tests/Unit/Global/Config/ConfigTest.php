<?php

namespace Tests\Unit\Global\Config;

it('cors config tiene estructura correcta', function () {
        $cors = config('cors');

        expect($cors)->toBeArray();
        expect($cors['paths'])->toContain('api/*');
        expect($cors['paths'])->toContain('sanctum/csrf-cookie');
        expect($cors['allowed_methods'])->toEqual(['*']);
        expect($cors['allowed_origins'])->toEqual(['*']);
        expect($cors['allowed_headers'])->toEqual(['*']);
    });

    it('auth config tiene estructura correcta', function () {
        $auth = config('auth');

        expect($auth['defaults']['guard'])->toEqual('web');
        expect($auth['defaults']['passwords'])->toEqual('users');
        expect($auth['guards']['web']['driver'])->toEqual('session');
        expect($auth['providers']['users']['model'])->toEqual(\App\Models\Usuario::class);
    });

    it('scribe config tiene estructura correcta', function () {
        $scribe = config('scribe');

        expect($scribe['type'])->toEqual('static');
        expect($scribe['base_url'])->toEqual('http://127.0.0.1:8000');
        expect($scribe['try_it_out']['enabled'])->toBeTrue();
        expect($scribe['postman']['enabled'])->toBeTrue();
        expect($scribe['openapi']['enabled'])->toBeTrue();
    });

    it('permission config tiene estructura correcta', function () {
        $perm = config('permission');

        expect($perm['teams'])->toBeFalse();
        expect($perm['enable_wildcard_permission'])->toBeFalse();
        expect($perm['register_permission_check_method'])->toBeTrue();
        expect($perm['cache'])->toHaveKey('expiration_time');
    });

    it('database config tiene conexion mysql', function () {
        $db = config('database');

        expect($db['connections'])->toHaveKey('mysql');
        expect($db['connections']['mysql']['charset'])->toEqual('utf8mb4');
        expect($db['connections']['mysql']['collation'])->toEqual('utf8mb4_unicode_ci');
    });

    it('filesystems config tiene disks local y public', function () {
        $fs = config('filesystems');

        expect($fs['default'])->toEqual('local');
        expect($fs['disks'])->toHaveKey('local');
        expect($fs['disks'])->toHaveKey('public');
    });
