<?php

namespace App\Models\Despacho;

use Illuminate\Database\Eloquent\Model;

class HistorialEstadoDespacho extends Model
{
    protected $table = 'historial_estado_despacho';
    protected $primaryKey = 'id_historial';
    public $timestamps = false;

    protected $fillable = [
        'id_despacho',
        'id_estado_despacho',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function despacho()
    {
        return $this->belongsTo(Despacho::class, 'id_despacho');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoDespacho::class, 'id_estado_despacho');
    }
}
