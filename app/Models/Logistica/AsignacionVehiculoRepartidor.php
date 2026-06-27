<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionVehiculoRepartidor extends Model
{
    use HasFactory;

    protected $table = 'asignaciones_vehiculo_repartidor';
    protected $primaryKey = 'id_asignacion';

    protected $fillable = [
        'id_repartidor',
        'id_vehiculo',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'activo' => 'boolean',
    ];

    public function repartidor()
    {
        return $this->belongsTo(\App\Models\Repartidor\Repartidor::class, 'id_repartidor');
    }

    public function vehiculo()
    {
        return $this->belongsTo(\App\Models\Vehiculo\Vehiculo::class, 'id_vehiculo');
    }
}
