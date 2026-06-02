<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'despacho_id',
        'repartidor_id',
        'tipo',
        'estado',
    ];

    public function despacho()
    {
        return $this->belongsTo(Despacho::class);
    }

    public function repartidor()
    {
        return $this->belongsTo(Repartidor::class);
    }
}
