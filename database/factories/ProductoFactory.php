<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition()
    {
        $nombres = [
            'Paracetamol', 'Ibuprofeno', 'Aspirina', 'Diclofenaco', 'Naproxeno',
            'Amoxicilina', 'Azitromicina', 'Ciprofloxacino', 'Ceftriaxona', 'Loratadina',
        ];
        $presentaciones = ['500mg', '1g', '200mg', '100mg', '400mg'];

        return [
            'categoria_id' => \App\Models\Categoria::inRandomOrder()->first()->id ?? \App\Models\Categoria::factory(),
            'laboratorio_id' => \App\Models\Laboratorio::inRandomOrder()->first()->id ?? \App\Models\Laboratorio::factory(),
            'codigo' => strtoupper($this->faker->unique()->bothify('PROD-####-???')),
            'nombre' => $this->faker->randomElement($nombres).' '.$this->faker->randomElement($presentaciones),
            'registro_sanitario' => $this->faker->unique()->bothify('RS-#####-##'),
        ];
    }
}
