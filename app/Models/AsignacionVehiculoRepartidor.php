<?php

namespace App\Models;

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
        'fecha_asignacion',
        'fecha_devolucion',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_devolucion' => 'datetime',
    ];

    public function repartidor()
    {
        return $this->belongsTo(Repartidor::class, 'id_repartidor');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'id_vehiculo');
    }
}
