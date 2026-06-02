<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'repartidor_id',
        'placa',
        'marca',
        'modelo',
    ];

    public function repartidor()
    {
        return $this->belongsTo(Repartidor::class);
    }
}
