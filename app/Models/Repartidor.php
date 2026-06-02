<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Repartidor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'repartidores';

    protected $fillable = [
        'user_id',
        'licencia',
        'tipo_licencia',
        'vencimiento_licencia',
    ];

    protected $casts = [
        'vencimiento_licencia' => 'date',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class);
    }

    public function despachos()
    {
        return $this->hasMany(Despacho::class);
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class);
    }

    public function ubicacionesGps()
    {
        return $this->hasMany(UbicacionGps::class);
    }
}
