<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repartidor extends Model
{
    use HasFactory;

    /**
     * Único origen de verdad del estado y la licencia.
     * Ver migración 2026_07_10_150004_create_repartidores_table.php
     * Añadir valores nuevos AL FINAL para no renumerar los ids derivados.
     */
    public const ESTADOS = [
        'activo'   => 'Activo',
        'inactivo' => 'Inactivo',
        'en_ruta'  => 'En ruta',
    ];

    public const LICENCIAS = [
        'A' => 'Categoría A',
        'B' => 'Categoría B',
        'C' => 'Categoría C',
    ];

    protected $table = 'repartidores';
    protected $primaryKey = 'id_repartidor';

    protected $fillable = [
        'id_usuario',
        'ci',
        'categoria_licencia',
        'estado',
        'firebase_uid',
    ];

    protected $casts = [
        'categoria_licencia' => 'string',
        'estado' => 'string',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function asignacionesVehiculo()
    {
        return $this->hasMany(AsignacionVehiculoRepartidor::class, 'id_repartidor');
    }

    public function asignacionActiva()
    {
        return $this->hasOne(AsignacionVehiculoRepartidor::class, 'id_repartidor')
            ->whereNull('fecha_devolucion')
            ->latestOfMany('fecha_asignacion');
    }

    public function hojasRuta()
    {
        return $this->hasMany(HojaRuta::class, 'id_repartidor');
    }

    public function cajas()
    {
        return $this->hasMany(CajaRepartidor::class, 'id_repartidor');
    }

    /** Catálogo de estados en la forma que consume el front-end. */
    public static function estadosCatalogo(): array
    {
        $catalogo = [];
        foreach (array_keys(self::ESTADOS) as $i => $clave) {
            $catalogo[] = [
                'id_estado_repartidor' => $i + 1,
                'clave'                => $clave,
                'nombre_estado'        => self::ESTADOS[$clave],
            ];
        }

        return $catalogo;
    }

    /** Catálogo de licencias en la forma que consume el front-end. */
    public static function licenciasCatalogo(): array
    {
        $catalogo = [];
        foreach (array_keys(self::LICENCIAS) as $i => $clave) {
            $catalogo[] = [
                'id_licencia' => $i + 1,
                'clave'       => $clave,
                'categoria'   => $clave,
                'nombre'      => self::LICENCIAS[$clave],
            ];
        }

        return $catalogo;
    }
}
