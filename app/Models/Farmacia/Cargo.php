<?php

namespace App\Models\Farmacia;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    protected $table = 'cargos';
    protected $primaryKey = 'id_cargo';
    public $timestamps = false;

    protected $fillable = ['nombre_cargo'];

    public function contactos()
    {
        return $this->hasMany(ContactoFarmacia::class, 'id_cargo');
    }
}
