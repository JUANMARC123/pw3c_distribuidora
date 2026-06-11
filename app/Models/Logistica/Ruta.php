<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    use HasFactory;
    protected $table = 'rutas';
    protected $primaryKey = 'id_ruta';
    public $timestamps = false;

    protected $fillable = ['nombre_ruta'];

    public function paradas()
    {
        return $this->hasMany(RutaParada::class, 'id_ruta')->orderBy('orden_parada');
    }

    public function controles()
    {
        return $this->hasMany(ControlRuta::class, 'id_ruta');
    }
}
