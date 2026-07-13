<?php

use App\Providers\EventServiceProvider;

it('tiene evento Registered en listen', function () {
        // Preparar
        $provider = new EventServiceProvider(app());
        $reflection = new ReflectionProperty($provider, 'listen');
        $reflection->setAccessible(true);
        $listen = $reflection->getValue($provider);

        // Verificar
        expect($listen)->toHaveKey(\Illuminate\Auth\Events\Registered::class);
        expect($listen[\Illuminate\Auth\Events\Registered::class])->toContain(\Illuminate\Auth\Listeners\SendEmailVerificationNotification::class);
    });

    it('registra subscribers en boot', function () {
        // Preparar
        $provider = new EventServiceProvider(app());

        $reflection = new ReflectionMethod($provider, 'boot');
        $reflection->setAccessible(true);

        // Actuar
        $reflection->invoke($provider);

        // Verificar
        $listeners = \Illuminate\Support\Facades\Event::getRawListeners();

        expect($listeners)->toHaveKey(\App\Events\RutaIniciada::class);
        expect($listeners)->toHaveKey(\App\Events\EntregaCompletada::class);
        expect($listeners)->toHaveKey(\App\Events\EntregaFallida::class);
        expect($listeners)->toHaveKey(\App\Events\DevolucionProducto::class);
        expect($listeners)->toHaveKey(\App\Events\RepartidorRegresa::class);
    });
