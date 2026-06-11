<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Model;

class EstadoRepartidor extends Model
{
    protected $table = 'estados_repartidor';
    protected $primaryKey = 'id_estado_repartidor';
    public $timestamps = false;

    protected $fillable = ['nombre_estado'];

    public function repartidores()
    {
        return $this->hasMany(Repartidor::class, 'id_estado_repartidor');
    }

    public function historiales()
    {
        return $this->hasMany(HistorialEstadoRepartidor::class, 'id_estado_repartidor');
    }
}
