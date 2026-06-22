<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class TipoMovimiento extends Model
{
    protected $table = 'tipos_movimiento';
    protected $primaryKey = 'id_tipo_movimiento';
    public $timestamps = false;

    protected $fillable = ['nombre_tipo'];

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'id_tipo_movimiento');
    }
}
