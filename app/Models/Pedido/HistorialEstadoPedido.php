<?php

namespace App\Models\Pedido;

use Illuminate\Database\Eloquent\Model;

class HistorialEstadoPedido extends Model
{
    protected $table = 'historial_estado_pedido';
    protected $primaryKey = 'id_historial';
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'id_estado_pedido',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoPedido::class, 'id_estado_pedido');
    }
}
