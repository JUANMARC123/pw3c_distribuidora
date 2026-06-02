<?php

namespace Database\Factories;

use App\Models\FormaFarmaceutica;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormaFarmaceuticaFactory extends Factory
{
    protected $model = FormaFarmaceutica::class;

    public function definition()
    {
        $formas = [
            'Tableta', 'Cápsula', 'Jarabe', 'Suspensión', 'Inyectable',
            'Crema', 'Ungüento', 'Gotas', 'Supositorio', 'Parche Transdérmico',
        ];

        return [
            'nombre' => $this->faker->unique()->randomElement($formas),
        ];
    }
}
