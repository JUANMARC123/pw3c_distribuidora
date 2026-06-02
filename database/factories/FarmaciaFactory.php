<?php

namespace Database\Factories;

use App\Models\Farmacia;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FarmaciaFactory extends Factory
{
    protected $model = Farmacia::class;

    public function definition()
    {
        $nombre = 'Farmacia '.$this->faker->randomElement(['San ', 'Santa ', 'Virgen de ', 'Don ']).Str::title($this->faker->word());
        $citiesCoords = [
            ['lat' => -17.3935, 'lng' => -66.1570], // Cochabamba
            ['lat' => -16.5000, 'lng' => -68.1500], // La Paz
            ['lat' => -17.7833, 'lng' => -63.1821], // Santa Cruz
            ['lat' => -19.5833, 'lng' => -65.7500], // Sucre
            ['lat' => -21.5333, 'lng' => -64.7333], // Tarija
        ];
        $coord = $this->faker->randomElement($citiesCoords);

        return [
            'nombre' => $nombre,
            'nit' => $this->faker->unique()->numerify('#######'),
            'categoria_id' => \App\Models\Categoria::inRandomOrder()->first()->id ?? \App\Models\Categoria::factory(),
            'logo' => 'logos/'.$this->faker->uuid().'.png',
            'telefono' => '+591 '.$this->faker->numerify('4#######'),
            'correo' => $this->faker->unique()->companyEmail(),
            'whatsapp' => '+591 '.$this->faker->numerify('7#######'),
            'direccion' => $this->faker->streetAddress().', '.$this->faker->city(),
            'latitud' => $coord['lat'] + $this->faker->randomFloat(7, -0.05, 0.05),
            'longitud' => $coord['lng'] + $this->faker->randomFloat(7, -0.05, 0.05),
            'es_24_horas' => $this->faker->boolean(40),
            'estado' => true,
        ];
    }
}
