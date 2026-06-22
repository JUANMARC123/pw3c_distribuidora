<?php

namespace App\Models\Devolucion;

use Illuminate\Database\Eloquent\Model;

class TipoDevolucion extends Model
{
    protected $table = 'tipos_devolucion';
    protected $primaryKey = 'id_tipo_devolucion';
    public $timestamps = false;

    protected $fillable = ['nombre_tipo'];
}
