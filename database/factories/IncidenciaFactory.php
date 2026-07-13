<?php

namespace Database\Factories;

use App\Models\Despacho;
use App\Models\Incidencia;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncidenciaFactory extends Factory
{
    protected $model = Incidencia::class;

    public function definition()
    {
        $tipos = ['cliente_ausente', 'producto_danado', 'retraso_trafico', 'devolucion_parcial', 'otro'];
        return [
            'id_despacho' => Despacho::factory(),
            'tipo_incidencia' => $this->faker->randomElement($tipos),
            'descripcion' => $this->faker->optional()->sentence(),
            'archivo' => $this->faker->optional()->uuid() . '.jpg',
            'fecha_incidencia' => now(),
        ];
    }
}
