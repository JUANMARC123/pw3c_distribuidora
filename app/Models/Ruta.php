<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'codigo_ruta',
        'fecha',
        'distancia_total',
        'tiempo_estimado',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'distancia_total' => 'decimal:2',
    ];

    public function paradas()
    {
        return $this->hasMany(RutaParada::class);
    }

    public function despachos()
    {
        return $this->hasMany(Despacho::class);
    }
}
