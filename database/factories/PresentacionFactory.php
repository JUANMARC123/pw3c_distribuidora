<?php

namespace Database\Factories;

use App\Models\FormaFarmaceutica;
use App\Models\Presentacion;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class PresentacionFactory extends Factory
{
    protected $model = Presentacion::class;

    public function definition()
    {
        return [
            'producto_id' => Producto::inRandomOrder()->first()->id ?? Producto::factory(),
            'forma_farmaceutica_id' => FormaFarmaceutica::inRandomOrder()->first()->id ?? FormaFarmaceutica::factory(),
            'descripcion' => 'Caja x '.$this->faker->numberBetween(10, 100).' unidades',
            'contenido' => $this->faker->randomElement(['500mg', '1g', '250mg/5ml', '100ml', '50ml']),
        ];
    }
}
