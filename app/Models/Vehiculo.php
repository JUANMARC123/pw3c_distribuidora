<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    /**
     * Único origen de verdad del estado del vehículo.
     * Ver migración 2026_07_10_150005_create_vehiculos_table.php
     */
    public const ESTADOS = [
        'disponible' => 'Disponible',
        'taller'     => 'En taller',
        'en_ruta'    => 'En ruta',
    ];

    protected $table = 'vehiculos';
    protected $primaryKey = 'id_vehiculo';

    protected $fillable = [
        'placa',
        'marca',
        'modelo',
        'capacidad_kg',
        'estado',
    ];

    protected $casts = [
        'estado'       => 'string',
        'capacidad_kg' => 'integer',
    ];

    public function asignaciones()
    {
        return $this->hasMany(AsignacionVehiculoRepartidor::class, 'id_vehiculo');
    }

    public function hojasRuta()
    {
        return $this->hasMany(HojaRuta::class, 'id_vehiculo');
    }

    /** Catálogo de estados en la forma que consume el front-end. */
    public static function estadosCatalogo(): array
    {
        $catalogo = [];
        foreach (array_keys(self::ESTADOS) as $i => $clave) {
            $catalogo[] = [
                'id_estado_vehiculo' => $i + 1,
                'clave'              => $clave,
                'nombre_estado'      => self::ESTADOS[$clave],
            ];
        }

        return $catalogo;
    }
}
