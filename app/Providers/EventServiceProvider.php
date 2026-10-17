<?php

namespace App\Providers;

use App\Events\RutaIniciada;
use App\Events\EntregaCompletada;
use App\Events\EntregaFallida;
use App\Events\RepartidorRegresa;
use App\Listeners\ActualizarEstadoRepartidorListener;
use App\Listeners\ActualizarEstadoVehiculoListener;
use App\Listeners\SyncRutaToFirebaseListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    public function boot()
    {
        Event::subscribe(ActualizarEstadoRepartidorListener::class);
        Event::subscribe(ActualizarEstadoVehiculoListener::class);
        Event::subscribe(SyncRutaToFirebaseListener::class);
    }
}
