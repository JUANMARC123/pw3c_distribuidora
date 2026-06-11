<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlRuta extends Model
{
    use HasFactory;
    protected $table = 'control_rutas';
    protected $primaryKey = 'id_control_ruta';
    public $timestamps = false;

    protected $fillable = [
        'id_ruta',
        'fecha_ruta',
        'hora_salida',
        'hora_llegada_real',
        'id_repartidor',
        'id_vehiculo',
    ];

    protected $casts = [
        'fecha_ruta' => 'date',
        'hora_salida' => 'string',
        'hora_llegada_real' => 'string',
    ];

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'id_ruta');
    }

    public function repartidor()
    {
        return $this->belongsTo(\App\Models\Repartidor\Repartidor::class, 'id_repartidor');
    }

    public function vehiculo()
    {
        return $this->belongsTo(\App\Models\Vehiculo\Vehiculo::class, 'id_vehiculo');
    }

    public function despachos()
    {
        return $this->hasMany(\App\Models\Despacho\Despacho::class, 'id_control_ruta');
    }
}
