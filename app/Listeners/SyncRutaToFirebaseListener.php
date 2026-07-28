<?php

namespace App\Listeners;

use App\Events\RutaIniciada;
use App\Events\RepartidorRegresa;
use App\Services\FirebaseSyncService;

class SyncRutaToFirebaseListener
{
    protected FirebaseSyncService $firebaseSync;

    public function __construct(FirebaseSyncService $firebaseSync)
    {
        $this->firebaseSync = $firebaseSync;
    }

    public function onRutaIniciada(RutaIniciada $event): void
    {
        $this->firebaseSync->iniciarViaje($event->hojaRuta);
    }

    public function onRepartidorRegresa(RepartidorRegresa $event): void
    {
        $this->firebaseSync->limpiarViaje($event->hojaRuta);
    }

    public function subscribe($events): void
    {
        $events->listen(RutaIniciada::class, [self::class, 'onRutaIniciada']);
        $events->listen(RepartidorRegresa::class, [self::class, 'onRepartidorRegresa']);
    }
}
