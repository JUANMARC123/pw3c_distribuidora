<?php

namespace App\Models\Devolucion;

use Illuminate\Database\Eloquent\Model;

class EstadoDevolucion extends Model
{
    protected $table = 'estados_devolucion';
    protected $primaryKey = 'id_estado_devolucion';
    public $timestamps = false;

    protected $fillable = ['nombre_estado'];
}
