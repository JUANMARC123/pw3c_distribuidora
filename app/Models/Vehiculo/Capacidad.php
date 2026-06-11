<?php

namespace App\Models\Vehiculo;

use Illuminate\Database\Eloquent\Model;

class Capacidad extends Model
{
    protected $table = 'capacidades';
    protected $primaryKey = 'id_capacidad';
    public $timestamps = false;

    protected $fillable = ['capacidad_kg'];

    protected $casts = [
        'capacidad_kg' => 'decimal:2',
    ];

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'id_capacidad');
    }
}
