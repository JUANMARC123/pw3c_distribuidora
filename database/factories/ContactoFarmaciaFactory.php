<?php

namespace Database\Factories;

use App\Models\ContactoFarmacia;
use App\Models\Farmacia;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactoFarmaciaFactory extends Factory
{
    protected $model = ContactoFarmacia::class;

    public function definition()
    {
        $cargos = ['Gerente', 'Propietario', 'Encargado de Compras', 'Administrador', 'Farmacéutico Titular', 'Asistente'];

        return [
            'farmacia_id' => Farmacia::inRandomOrder()->first()->id ?? Farmacia::factory(),
            'nombre' => $this->faker->name(),
            'cargo' => $this->faker->randomElement($cargos),
            'telefono' => '+591 '.$this->faker->numerify('7#######'),
            'correo' => $this->faker->unique()->safeEmail(),
        ];
    }
}
