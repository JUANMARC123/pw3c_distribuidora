<?php

namespace App\Models\Repartidor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repartidor extends Model
{
    use HasFactory;
    protected $table = 'repartidores';
    protected $primaryKey = 'id_repartidor';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'ci',
        'id_extension_ci',
        'id_licencia',
        'id_estado_repartidor',
    ];

    public function usuario()
    {
        return $this->belongsTo(\App\Models\Seguridad\Usuario::class, 'id_usuario');
    }

    public function extensionCi()
    {
        return $this->belongsTo(ExtensionCI::class, 'id_extension_ci');
    }

    public function licencia()
    {
        return $this->belongsTo(Licencia::class, 'id_licencia');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoRepartidor::class, 'id_estado_repartidor');
    }

    public function historiales()
    {
        return $this->hasMany(HistorialEstadoRepartidor::class, 'id_repartidor');
    }

    public function controlRutas()
    {
        return $this->hasMany(\App\Models\Logistica\ControlRuta::class, 'id_repartidor');
    }
}
