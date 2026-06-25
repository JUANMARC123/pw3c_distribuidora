<?php

namespace App\Models\Producto;

use Illuminate\Database\Eloquent\Model;

class EstadoDetalle extends Model
{
    protected $table = 'estados_detalle';
    protected $primaryKey = 'id_estado_detalle';
    public $timestamps = false;

    protected $fillable = ['nombre_estado'];

    public function detalles()
    {
        return $this->hasMany(\App\Models\Pedido\DetallePedido::class, 'id_estado_detalle');
    }
}
