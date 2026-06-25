<?php

namespace App\Models\Pedido;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    use HasFactory;
    protected $table = 'detalle_pedido';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'id_producto',
        'cantidad_solicitada',
        'cantidad_entregada',
        'precio_unitario',
        'subtotal',
        'id_estado_detalle',
    ];

    protected $casts = [
        'cantidad_solicitada' => 'decimal:2',
        'cantidad_entregada' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function producto()
    {
        return $this->belongsTo(\App\Models\Producto\Producto::class, 'id_producto');
    }

    public function estado()
    {
        return $this->belongsTo(\App\Models\Producto\EstadoDetalle::class, 'id_estado_detalle');
    }
}
