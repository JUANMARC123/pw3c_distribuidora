<?php

namespace App\Models\Compra;

use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model
{
    protected $table = 'detalles_compra';
    protected $primaryKey = 'id_detalle_compra';
    public $timestamps = false;

    protected $fillable = ['id_orden_compra', 'id_producto', 'cantidad', 'precio_unitario', 'subtotal'];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function ordenCompra()
    {
        return $this->belongsTo(OrdenCompra::class, 'id_orden_compra');
    }

    public function producto()
    {
        return $this->belongsTo(\App\Models\Medicamento\Producto::class, 'id_producto');
    }
}
