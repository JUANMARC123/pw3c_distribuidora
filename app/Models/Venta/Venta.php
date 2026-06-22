<?php

namespace App\Models\Venta;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';
    protected $primaryKey = 'id_venta';
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'id_usuario',
        'id_estado_venta',
        'fecha_venta',
        'total',
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'total' => 'decimal:2',
    ];

    public function pedido()
    {
        return $this->belongsTo(\App\Models\Pedido\Pedido::class, 'id_pedido');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\Seguridad\Usuario::class, 'id_usuario');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoVenta::class, 'id_estado_venta');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'id_venta');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_venta');
    }
}
