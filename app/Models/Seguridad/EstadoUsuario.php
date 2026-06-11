<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;

class EstadoUsuario extends Model
{
    protected $table = 'estados_usuario';
    protected $primaryKey = 'id_estado_usuario';
    public $timestamps = false;

    protected $fillable = ['nombre_estado'];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_estado_usuario');
    }
}
