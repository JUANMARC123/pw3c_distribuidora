<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';
    protected $primaryKey = 'id_pedido';

    protected $fillable = [
        'id_farmacia',
        'id_usuario',
        'estado',
        'metodo_pago',
        'fecha_pedido',
        'observaciones',
        'subtotal',
        'descuento',
        'total',
        'archivo_pedido',
    ];

    protected $casts = [
        'fecha_pedido' => 'datetime',
        'estado' => 'string',
        'metodo_pago' => 'string',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function farmacia()
    {
        return $this->belongsTo(Farmacia::class, 'id_farmacia');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido');
    }

    public function despacho()
    {
        return $this->hasOne(Despacho::class, 'id_pedido');
    }

    public function hojaRutaDetalles()
    {
        return $this->hasMany(HojaRutaDetalle::class, 'id_pedido');
    }
}
