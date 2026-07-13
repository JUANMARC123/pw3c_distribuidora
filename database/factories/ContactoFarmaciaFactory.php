<?php

namespace Database\Factories;

use App\Models\ContactoFarmacia;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactoFarmaciaFactory extends Factory
{
    protected $model = ContactoFarmacia::class;

    public function definition()
    {
        return [
            'id_farmacia' => \App\Models\Farmacia::factory(),
            'nombre_contacto' => $this->faker->name(),
            'cargo' => $this->faker->jobTitle(),
            'telefono' => $this->faker->phoneNumber(),
            'email' => $this->faker->email(),
        ];
    }
}
