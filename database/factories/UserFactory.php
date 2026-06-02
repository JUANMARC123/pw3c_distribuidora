<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition()
    {
        $nombre = $this->faker->firstName();
        $apellido = $this->faker->lastName();

        return [
            'name' => $nombre.' '.$apellido,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'telefono' => '+591 '.$this->faker->numerify('7#######'),
            'estado' => true,
            'ultimo_acceso' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
