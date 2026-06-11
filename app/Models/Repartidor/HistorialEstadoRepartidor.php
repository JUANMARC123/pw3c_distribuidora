<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;

class HistorialEstadoRepartidor extends Model
{
    protected $table = 'historial_estado_repartidor';
    protected $primaryKey = 'id_historial';
    public $timestamps = false;

    protected $fillable = [
        'id_repartidor',
        'id_estado_repartidor',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function repartidor()
    {
        return $this->belongsTo(Repartidor::class, 'id_repartidor');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoRepartidor::class, 'id_estado_repartidor');
    }
}
