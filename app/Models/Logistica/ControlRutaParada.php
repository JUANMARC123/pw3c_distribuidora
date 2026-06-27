<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlRutaParada extends Model
{
    use HasFactory;

    protected $table = 'control_ruta_paradas';
    protected $primaryKey = 'id_control_ruta_parada';

    protected $fillable = [
        'id_control_ruta',
        'id_pedido',
        'id_farmacia',
        'orden_parada',
        'hora_estimada',
        'hora_llegada_real',
    ];

    protected $casts = [
        'hora_estimada' => 'string',
        'hora_llegada_real' => 'string',
    ];

    public function controlRuta()
    {
        return $this->belongsTo(ControlRuta::class, 'id_control_ruta');
    }

    public function pedido()
    {
        return $this->belongsTo(\App\Models\Pedido\Pedido::class, 'id_pedido');
    }

    public function farmacia()
    {
        return $this->belongsTo(\App\Models\Farmacia\Farmacia::class, 'id_farmacia');
    }
}
