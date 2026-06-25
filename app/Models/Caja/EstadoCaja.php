<?php

namespace App\Models\Caja;

use Illuminate\Database\Eloquent\Model;

class EstadoCaja extends Model
{
    protected $table = 'estados_caja';
    protected $primaryKey = 'id_estado_caja';
    public $timestamps = false;

    protected $fillable = ['nombre_estado'];

    public function cajas()
    {
        return $this->hasMany(CajaRepartidor::class, 'id_estado_caja');
    }
}
