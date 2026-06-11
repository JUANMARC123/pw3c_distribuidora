<?php

namespace App\Models\Logistica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RutaParada extends Model
{
    use HasFactory;
    protected $table = 'ruta_paradas';
    protected $primaryKey = 'id_parada';
    public $timestamps = false;

    protected $fillable = [
        'id_ruta',
        'id_farmacia',
        'orden_parada',
        'hora_estimada',
    ];

    protected $casts = [
        'hora_estimada' => 'string',
    ];

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'id_ruta');
    }

    public function farmacia()
    {
        return $this->belongsTo(\App\Models\Farmacia\Farmacia::class, 'id_farmacia');
    }

    public function despachos()
    {
        return $this->hasMany(\App\Models\Despacho\Despacho::class, 'id_parada');
    }
}
