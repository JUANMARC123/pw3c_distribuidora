<?php

namespace App\Models\Despacho;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Despacho extends Model
{
    use HasFactory;
    protected $table = 'despachos';
    protected $primaryKey = 'id_despacho';
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'id_parada',
        'id_control_ruta',
        'fecha_hora_despacho',
        'id_estado_despacho',
    ];

    protected $casts = [
        'fecha_hora_despacho' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(\App\Models\Pedido\Pedido::class, 'id_pedido');
    }

    public function parada()
    {
        return $this->belongsTo(\App\Models\Logistica\RutaParada::class, 'id_parada');
    }

    public function controlRuta()
    {
        return $this->belongsTo(\App\Models\Logistica\ControlRuta::class, 'id_control_ruta');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoDespacho::class, 'id_estado_despacho');
    }

    public function historiales()
    {
        return $this->hasMany(HistorialEstadoDespacho::class, 'id_despacho');
    }

    public function incidencias()
    {
        return $this->hasMany(\App\Models\Evidencia\Incidencia::class, 'id_despacho');
    }

    public function evidencias()
    {
        return $this->hasMany(\App\Models\Evidencia\EvidenciaEntrega::class, 'id_despacho');
    }
}
