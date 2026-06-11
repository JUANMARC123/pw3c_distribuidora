<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $table = 'modulos';
    protected $primaryKey = 'id_modulo';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function permisos()
    {
        return $this->hasMany(Permiso::class, 'id_modulo');
    }
}
