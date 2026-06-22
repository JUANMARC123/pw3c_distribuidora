<?php

namespace App\Models\Compra;

use Illuminate\Database\Eloquent\Model;

class EstadoOrdenCompra extends Model
{
    protected $table = 'estados_orden_compra';
    protected $primaryKey = 'id_estado_orden_compra';
    public $timestamps = false;

    protected $fillable = ['nombre_estado'];

    public function ordenesCompra()
    {
        return $this->hasMany(OrdenCompra::class, 'id_estado_orden_compra');
    }
}
