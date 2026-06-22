<?php

namespace App\Models\Devolucion;

use Illuminate\Database\Eloquent\Model;

class HistorialEstadoDevolucion extends Model
{
    protected $table = 'historial_estado_devolucion';
    protected $primaryKey = 'id_historial';
    public $timestamps = false;

    protected $fillable = [
        'id_devolucion',
        'id_estado_devolucion',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function devolucion()
    {
        return $this->belongsTo(Devolucion::class, 'id_devolucion');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoDevolucion::class, 'id_estado_devolucion');
    }
}
