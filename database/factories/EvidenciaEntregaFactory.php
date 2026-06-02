<?php

namespace Database\Factories;

use App\Models\Despacho;
use App\Models\EvidenciaEntrega;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvidenciaEntregaFactory extends Factory
{
    protected $model = EvidenciaEntrega::class;

    public function definition()
    {
        return [
            'despacho_id' => Despacho::inRandomOrder()->first()->id ?? Despacho::factory(),
            'foto' => 'evidencias/foto_'.$this->faker->uuid().'.jpg',
            'firma' => 'firmas/firma_'.$this->faker->uuid().'.png',
            'comentario' => $this->faker->boolean(60)
                ? $this->faker->randomElement([
                    'Entrega realizada con éxito',
                    'Recibido conforme por el encargado',
                    'Entrega parcial por falta de stock',
                    'Cliente satisfecho con el producto',
                ])
                : null,
        ];
    }
}
