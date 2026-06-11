<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Sanctum::authenticateAccessTokensUsing(function ($token, $isValid) {
            return $token->tokenable->id_estado_usuario === 1 && $isValid;
        });
    }
}
