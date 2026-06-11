<?php

namespace App\Models\Vehiculo;

use Illuminate\Database\Eloquent\Model;

class EstadoVehiculo extends Model
{
    protected $table = 'estados_vehiculo';
    protected $primaryKey = 'id_estado_vehiculo';
    public $timestamps = false;

    protected $fillable = ['nombre_estado'];

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'id_estado_vehiculo');
    }

    public function historiales()
    {
        return $this->hasMany(HistorialEstadoVehiculo::class, 'id_estado_vehiculo');
    }
}
