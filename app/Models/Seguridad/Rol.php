<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_roles', 'id_rol', 'id_usuario');
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'rol_permiso', 'id_rol', 'id_permiso');
    }
}
