<?php

namespace App\Models\Devolucion;

use Illuminate\Database\Eloquent\Model;

class DetalleDevolucion extends Model
{
    protected $table = 'detalles_devolucion';
    protected $primaryKey = 'id_detalle_devolucion';
    public $timestamps = false;

    protected $fillable = [
        'id_devolucion',
        'id_producto',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'motivo_detalle',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function devolucion()
    {
        return $this->belongsTo(Devolucion::class, 'id_devolucion');
    }

    public function producto()
    {
        return $this->belongsTo(\App\Models\Medicamento\Producto::class, 'id_producto');
    }
}
