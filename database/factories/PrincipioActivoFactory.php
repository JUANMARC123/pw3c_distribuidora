<?php

namespace Database\Factories;

use App\Models\PrincipioActivo;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrincipioActivoFactory extends Factory
{
    protected $model = PrincipioActivo::class;

    public function definition()
    {
        $principios = [
            'Paracetamol', 'Ibuprofeno', 'Ácido Acetilsalicílico', 'Diclofenaco Sódico',
            'Amoxicilina Trihidrato', 'Azitromicina Dihidratada', 'Ciprofloxacino Clorhidrato',
            'Loratadina', 'Cetirizina', 'Omeprazol', 'Ranitidina', 'Metformina',
        ];

        return [
            'nombre' => $this->faker->unique()->randomElement($principios),
        ];
    }
}
