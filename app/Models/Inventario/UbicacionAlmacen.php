<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class UbicacionAlmacen extends Model
{
    protected $table = 'ubicaciones_almacen';
    protected $primaryKey = 'id_ubicacion';
    public $timestamps = false;

    protected $fillable = ['id_almacen', 'pasillo', 'estante'];

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'id_almacen');
    }

    public function inventario()
    {
        return $this->hasMany(Inventario::class, 'id_ubicacion');
    }
}
