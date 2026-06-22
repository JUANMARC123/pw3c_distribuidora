<?php

namespace App\Models\Medicamento;

use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $table = 'lotes';
    protected $primaryKey = 'id_lote';

    protected $fillable = [
        'id_producto',
        'codigo_lote',
        'fecha_fabricacion',
        'fecha_vencimiento',
        'precio_compra',
    ];

    protected $casts = [
        'fecha_fabricacion' => 'date',
        'fecha_vencimiento' => 'date',
        'precio_compra' => 'decimal:2',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}
