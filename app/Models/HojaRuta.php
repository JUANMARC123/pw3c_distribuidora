<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HojaRuta extends Model
{
    use HasFactory;

    protected $table = 'hojas_ruta';
    protected $primaryKey = 'id_hoja_ruta';

    protected $fillable = [
        'fecha',
        'hora_salida',
        'hora_llegada',
        'id_repartidor',
        'id_vehiculo',
        'estado_ruta',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_salida' => 'string',
        'hora_llegada' => 'string',
        'estado_ruta' => 'string',
    ];

    public function repartidor()
    {
        return $this->belongsTo(Repartidor::class, 'id_repartidor');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'id_vehiculo');
    }

    public function detalles()
    {
        return $this->hasMany(HojaRutaDetalle::class, 'id_hoja_ruta')->orderBy('orden_visita');
    }

    public function caja()
    {
        return $this->hasOne(CajaRepartidor::class, 'id_hoja_ruta');
    }
}
