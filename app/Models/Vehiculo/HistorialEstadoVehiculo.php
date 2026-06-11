<?php

namespace App\Models\Vehiculo;

use Illuminate\Database\Eloquent\Model;

class HistorialEstadoVehiculo extends Model
{
    protected $table = 'historial_estado_vehiculo';
    protected $primaryKey = 'id_historial';
    public $timestamps = false;

    protected $fillable = [
        'id_vehiculo',
        'id_estado_vehiculo',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'id_vehiculo');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoVehiculo::class, 'id_estado_vehiculo');
    }
}
