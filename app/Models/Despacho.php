<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Despacho extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'farmacia_id',
        'repartidor_id',
        'ruta_id',
        'user_id',
        'codigo_despacho',
        'fecha_salida',
        'fecha_entrega',
        'estado',
    ];

    protected $casts = [
        'fecha_salida' => 'datetime',
        'fecha_entrega' => 'datetime',
    ];

    public function farmacia()
    {
        return $this->belongsTo(Farmacia::class);
    }

    public function repartidor()
    {
        return $this->belongsTo(Repartidor::class);
    }

    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalleDespachos()
    {
        return $this->hasMany(DetalleDespacho::class);
    }

    public function evidencias()
    {
        return $this->hasMany(EvidenciaEntrega::class);
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }
}
