<?php

namespace App\Models\Pedido;

use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    protected $table = 'detalle_pedido';
    protected $primaryKey = 'id_detalle_pedido';
    public $timestamps = false;

    protected $fillable = ['id_pedido', 'id_producto', 'cantidad', 'precio_unitario', 'subtotal'];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function producto()
    {
        return $this->belongsTo(\App\Models\Medicamento\Producto::class, 'id_producto');
    }
}
