<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $table = 'inventario';
    protected $primaryKey = 'id_inventario';

    protected $fillable = [
        'id_producto',
        'id_lote',
        'id_ubicacion',
        'stock_actual',
        'stock_minimo',
        'precio_venta',
        'fecha_actualizacion',
    ];

    protected $casts = [
        'stock_actual' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'fecha_actualizacion' => 'datetime',
    ];

    public function producto()
    {
        return $this->belongsTo(\App\Models\Medicamento\Producto::class, 'id_producto');
    }

    public function lote()
    {
        return $this->belongsTo(\App\Models\Medicamento\Lote::class, 'id_lote');
    }

    public function ubicacion()
    {
        return $this->belongsTo(\App\Models\Inventario\UbicacionAlmacen::class, 'id_ubicacion');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'id_inventario');
    }
}
