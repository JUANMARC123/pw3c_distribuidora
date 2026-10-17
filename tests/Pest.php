<?php

use App\Models\Usuario;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class)->in('Feature');
uses(TestCase::class)->in('Unit');

function actingAsAdmin(): Usuario
{
    $user = Usuario::factory()->create();
    $user->assignRole('Administrador');
    Sanctum::actingAs($user, ['*']);
    return $user;
}

function actingAsSupervisor(): Usuario
{
    $user = Usuario::factory()->create();
    $user->assignRole('Supervisor');
    Sanctum::actingAs($user, ['*']);
    return $user;
}

function actingAsOperador(): Usuario
{
    $user = Usuario::factory()->create();
    $user->assignRole('Operador');
    Sanctum::actingAs($user, ['*']);
    return $user;
}

function actingAsRepartidor(): Usuario
{
    $user = Usuario::factory()->create();
    $user->assignRole('Repartidor');
    Sanctum::actingAs($user, ['*']);
    return $user;
}
