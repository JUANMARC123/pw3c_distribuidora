<?php

namespace Database\Factories;

use App\Models\Laboratorio;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaboratorioFactory extends Factory
{
    protected $model = Laboratorio::class;

    public function definition()
    {
        $laboratorios = [
            ['Bayer', 'Alemania'],
            ['Pfizer', 'Estados Unidos'],
            ['Bagó', 'Argentina'],
            ['Roche', 'Suiza'],
            ['Novartis', 'Suiza'],
            ['Sanofi', 'Francia'],
            ['GlaxoSmithKline', 'Reino Unido'],
            ['Merck', 'Estados Unidos'],
            ['Abbott', 'Estados Unidos'],
            ['Genfar', 'Colombia'],
        ];
        $l = $this->faker->unique()->randomElement($laboratorios);

        return [
            'nombre' => $l[0],
            'pais' => $l[1],
        ];
    }
}
