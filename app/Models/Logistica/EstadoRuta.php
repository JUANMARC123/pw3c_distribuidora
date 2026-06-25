<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Model;

class EstadoRuta extends Model
{
    protected $table = 'estados_ruta';
    protected $primaryKey = 'id_estado_ruta';
    public $timestamps = false;

    protected $fillable = ['nombre_estado'];

    public function controles()
    {
        return $this->hasMany(ControlRuta::class, 'id_estado_ruta');
    }
}
