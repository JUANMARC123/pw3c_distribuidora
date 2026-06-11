<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;

class SesionUsuario extends Model
{
    protected $table = 'sesiones_usuario';
    protected $primaryKey = 'id_sesion';
    public $timestamps = false;

    protected $fillable = ['id_usuario', 'fecha_inicio', 'fecha_fin'];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
