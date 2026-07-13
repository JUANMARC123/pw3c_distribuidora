<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculos';
    protected $primaryKey = 'id_vehiculo';

    protected $fillable = [
        'placa',
        'marca',
        'modelo',
        'estado',
    ];

    protected $casts = [
        'estado' => 'string',
    ];

    public function asignaciones()
    {
        return $this->hasMany(AsignacionVehiculoRepartidor::class, 'id_vehiculo');
    }

    public function hojasRuta()
    {
        return $this->hasMany(HojaRuta::class, 'id_vehiculo');
    }
}
