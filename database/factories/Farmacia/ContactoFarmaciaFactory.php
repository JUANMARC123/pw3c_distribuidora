<?php

namespace Database\Factories\Farmacia;

use App\Models\Farmacia\ContactoFarmacia;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactoFarmaciaFactory extends Factory
{
    protected $model = ContactoFarmacia::class;

    public function definition()
    {
        return [
            'id_farmacia' => 1,
            'nombre_contacto' => fake()->name(),
            'id_cargo' => fake()->numberBetween(1, 5),
            'telefono' => fake()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
        ];
    }
}
