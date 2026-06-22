<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';
    protected $primaryKey = 'id_movimiento';
    public $timestamps = false;

    protected $fillable = [
        'id_inventario',
        'id_tipo_movimiento',
        'id_usuario',
        'cantidad',
        'stock_anterior',
        'stock_posterior',
        'referencia',
        'observaciones',
        'created_at',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'stock_anterior' => 'decimal:2',
        'stock_posterior' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'id_inventario');
    }

    public function tipoMovimiento()
    {
        return $this->belongsTo(TipoMovimiento::class, 'id_tipo_movimiento');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\Seguridad\Usuario::class, 'id_usuario');
    }
}
