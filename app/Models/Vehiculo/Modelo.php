<?php

namespace App\Models\Vehiculo;

use Illuminate\Database\Eloquent\Model;

class Modelo extends Model
{
    protected $table = 'modelos';
    protected $primaryKey = 'id_modelo';
    public $timestamps = false;

    protected $fillable = ['id_marca', 'nombre_modelo'];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca');
    }

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'id_modelo');
    }
}
