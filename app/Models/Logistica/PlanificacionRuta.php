<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanificacionRuta extends Model
{
    use HasFactory;
    protected $table = 'planificacion_rutas';
    protected $primaryKey = 'id_planificacion';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_control_ruta',
        'estado',
        'datos_json',
        'fecha_creacion',
        'fecha_confirmacion',
    ];

    protected $casts = [
        'datos_json' => 'array',
        'fecha_creacion' => 'datetime',
        'fecha_confirmacion' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(\App\Models\Seguridad\Usuario::class, 'id_usuario');
    }

    public function controlRuta()
    {
        return $this->belongsTo(ControlRuta::class, 'id_control_ruta');
    }
}
