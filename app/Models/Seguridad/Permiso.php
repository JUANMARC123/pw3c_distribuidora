<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permisos';
    protected $primaryKey = 'id_permiso';
    public $timestamps = false;

    protected $fillable = ['id_modulo', 'id_accion'];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'id_modulo');
    }

    public function accion()
    {
        return $this->belongsTo(Accion::class, 'id_accion');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_permiso', 'id_permiso', 'id_rol');
    }
}
