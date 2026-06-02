<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UbicacionGps extends Model
{
    use HasFactory;

    protected $table = 'ubicaciones_gps';

    protected $fillable = [
        'repartidor_id',
        'latitud',
        'longitud',
        'velocidad',
        'fecha_hora',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'velocidad' => 'decimal:2',
        'fecha_hora' => 'datetime',
    ];

    public function repartidor()
    {
        return $this->belongsTo(Repartidor::class);
    }
}
