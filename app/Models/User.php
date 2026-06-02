<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'nombre',
        'apellido',
        'email',
        'password',
        'telefono',
        'estado',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'ultimo_acceso' => 'datetime',
        'estado' => 'boolean',
    ];

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'usuario_roles');
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class);
    }

    public function sesiones()
    {
        return $this->hasMany(SesionUsuario::class);
    }

    public function accesos()
    {
        return $this->hasMany(AccesoSistema::class);
    }

    public function repartidor()
    {
        return $this->hasOne(Repartidor::class);
    }

    public function despachos()
    {
        return $this->hasMany(Despacho::class);
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function geocercas()
    {
        return $this->hasMany(Geocerca::class);
    }
}
