<?php

namespace App\Models\Farmacia;

use Illuminate\Database\Eloquent\Model;

class EstadoFarmacia extends Model
{
    protected $table = 'estados_farmacia';
    protected $primaryKey = 'id_estado_farmacia';
    public $timestamps = false;

    protected $fillable = ['nombre_estado'];

    public function farmacias()
    {
        return $this->hasMany(Farmacia::class, 'id_estado_farmacia');
    }
}
