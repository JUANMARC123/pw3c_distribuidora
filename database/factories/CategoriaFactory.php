<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    public function definition()
    {
        $categorias = [
            ['Analgésicos', 'Medicamentos para aliviar el dolor'],
            ['Antiinflamatorios', 'Reducen la inflamación y dolor'],
            ['Antibióticos', 'Combaten infecciones bacterianas'],
            ['Antipiréticos', 'Reducen la fiebre'],
            ['Antihistamínicos', 'Tratamiento de alergias'],
            ['Vitaminas', 'Suplementos nutricionales'],
            ['Cuidado Personal', 'Productos de higiene y cuidado'],
            ['Cuidado Infantil', 'Productos para bebés y niños'],
            ['Cardiovascular', 'Medicamentos para el corazón'],
            ['Digestivos', 'Tratamiento de problemas digestivos'],
        ];
        $c = $this->faker->unique()->randomElement($categorias);

        return [
            'nombre' => $c[0],
            'descripcion' => $c[1],
        ];
    }
}
