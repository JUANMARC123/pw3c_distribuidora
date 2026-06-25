<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;

class HistorialEstadoRuta extends Model
{
    protected $table = 'historial_estado_ruta';
    protected $primaryKey = 'id_historial';
    public $timestamps = false;

    protected $fillable = [
        'id_control_ruta',
        'id_estado_ruta',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function controlRuta()
    {
        return $this->belongsTo(ControlRuta::class, 'id_control_ruta');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoRuta::class, 'id_estado_ruta');
    }
}
