<?php

namespace App\Models\Promocion;

use Illuminate\Database\Eloquent\Model;

class ProductoPromocion extends Model
{
    protected $table = 'productos_promocion';
    protected $primaryKey = 'id_producto_promocion';
    public $timestamps = false;

    protected $fillable = [
        'id_promocion',
        'id_producto',
        'cantidad_minima',
    ];

    protected $casts = [
        'cantidad_minima' => 'decimal:2',
    ];

    public function promocion()
    {
        return $this->belongsTo(Promocion::class, 'id_promocion');
    }

    public function producto()
    {
        return $this->belongsTo(\App\Models\Medicamento\Producto::class, 'id_producto');
    }
}
