<?php

namespace App\Models\Venta;

use Illuminate\Database\Eloquent\Model;

class EstadoVenta extends Model
{
    protected $table = 'estados_venta';
    protected $primaryKey = 'id_estado_venta';
    public $timestamps = false;

    protected $fillable = ['nombre_estado'];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_estado_venta');
    }
}
