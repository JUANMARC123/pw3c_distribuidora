<?php

namespace App\Models\Vehiculo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;
    protected $table = 'vehiculos';
    protected $primaryKey = 'id_vehiculo';
    public $timestamps = false;

    protected $fillable = [
        'placa',
        'id_modelo',
        'id_capacidad',
        'id_estado_vehiculo',
    ];

    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'id_modelo');
    }

    public function capacidad()
    {
        return $this->belongsTo(Capacidad::class, 'id_capacidad');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoVehiculo::class, 'id_estado_vehiculo');
    }

    public function historiales()
    {
        return $this->hasMany(HistorialEstadoVehiculo::class, 'id_vehiculo');
    }

    public function controlRutas()
    {
        return $this->hasMany(\App\Models\Logistica\ControlRuta::class, 'id_vehiculo');
    }
}
