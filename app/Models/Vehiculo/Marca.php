<?php

namespace App\Models\Vehiculo;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $table = 'marcas';
    protected $primaryKey = 'id_marca';
    public $timestamps = false;

    protected $fillable = ['nombre_marca'];

    public function modelos()
    {
        return $this->hasMany(Modelo::class, 'id_marca');
    }
}
