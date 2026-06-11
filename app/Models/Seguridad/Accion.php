<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;

class Accion extends Model
{
    protected $table = 'acciones';
    protected $primaryKey = 'id_accion';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function permisos()
    {
        return $this->hasMany(Permiso::class, 'id_accion');
    }
}
