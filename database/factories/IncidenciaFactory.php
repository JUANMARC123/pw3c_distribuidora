<?php

namespace Database\Factories;

use App\Models\Despacho;
use App\Models\Incidencia;
use App\Models\Repartidor;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncidenciaFactory extends Factory
{
    protected $model = Incidencia::class;

    public function definition()
    {
        $tipos = ['retraso', 'daño_producto', 'extravio', 'cliente_ausente', 'direccion_incorrecta', 'accidente_transito', 'clima_adverso'];
        $estados = ['reportada', 'en_revision', 'resuelta', 'cerrada'];

        return [
            'despacho_id' => Despacho::inRandomOrder()->first()->id ?? Despacho::factory(),
            'repartidor_id' => Repartidor::inRandomOrder()->first()->id ?? Repartidor::factory(),
            'tipo' => $this->faker->randomElement($tipos),
            'estado' => $this->faker->randomElement($estados),
        ];
    }
}
