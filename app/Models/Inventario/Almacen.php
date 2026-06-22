<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    protected $table = 'almacenes';
    protected $primaryKey = 'id_almacen';
    public $timestamps = false;

    protected $fillable = ['id_farmacia', 'nombre'];

    public function farmacia()
    {
        return $this->belongsTo(\App\Models\Farmacia\Farmacia::class, 'id_farmacia');
    }

    public function ubicaciones()
    {
        return $this->hasMany(UbicacionAlmacen::class, 'id_almacen');
    }
}
