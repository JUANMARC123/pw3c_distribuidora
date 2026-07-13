<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repartidor extends Model
{
    use HasFactory;

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
}
