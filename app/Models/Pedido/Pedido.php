<?php

namespace App\Models\Pedido;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;
    protected $table = 'pedidos';
    protected $primaryKey = 'id_pedido';
    public $timestamps = false;

    protected $fillable = [
        'id_farmacia',
        'id_contacto',
        'id_usuario',
        'id_estado_pedido',
        'fecha_pedido',
        'observaciones',
    ];

    protected $casts = [
        'fecha_pedido' => 'datetime',
    ];

    public function farmacia()
    {
        return $this->belongsTo(\App\Models\Farmacia\Farmacia::class, 'id_farmacia');
    }

    public function contacto()
    {
        return $this->belongsTo(\App\Models\Farmacia\ContactoFarmacia::class, 'id_contacto');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\Seguridad\Usuario::class, 'id_usuario');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoPedido::class, 'id_estado_pedido');
    }

    public function historiales()
    {
        return $this->hasMany(HistorialEstadoPedido::class, 'id_pedido');
    }

    public function despacho()
    {
        return $this->hasOne(\App\Models\Despacho\Despacho::class, 'id_pedido');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido');
    }
}
