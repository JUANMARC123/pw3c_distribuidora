<?php

namespace App\Models\Pedido;

use Illuminate\Database\Eloquent\Model;

class EstadoPedido extends Model
{
    protected $table = 'estados_pedido';
    protected $primaryKey = 'id_estado_pedido';
    public $timestamps = false;

    protected $fillable = ['nombre_estado'];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_estado_pedido');
    }

    public function historiales()
    {
        return $this->hasMany(HistorialEstadoPedido::class, 'id_estado_pedido');
    }
}
