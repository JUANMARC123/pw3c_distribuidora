<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RutaParada extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruta_id',
        'farmacia_id',
        'orden_parada',
    ];

    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }

    public function farmacia()
    {
        return $this->belongsTo(Farmacia::class);
    }
}
