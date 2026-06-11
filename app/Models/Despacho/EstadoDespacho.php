<?php

namespace App\Models\Despacho;

use Illuminate\Database\Eloquent\Model;

class EstadoDespacho extends Model
{
    protected $table = 'estados_despacho';
    protected $primaryKey = 'id_estado_despacho';
    public $timestamps = false;

    protected $fillable = ['nombre_estado'];

    public function despachos()
    {
        return $this->hasMany(Despacho::class, 'id_estado_despacho');
    }

    public function historiales()
    {
        return $this->hasMany(HistorialEstadoDespacho::class, 'id_estado_despacho');
    }
}
