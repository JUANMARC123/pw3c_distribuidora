<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'password_hash',
        'telefono',
        'id_estado_usuario',
        'fecha_creacion',
        'fecha_bloqueo',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_bloqueo' => 'datetime',
        'ultimo_acceso' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function estado()
    {
        return $this->belongsTo(EstadoUsuario::class, 'id_estado_usuario');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'usuario_roles', 'id_usuario', 'id_rol');
    }

    public function pedidos()
    {
        return $this->hasMany(\App\Models\Pedido\Pedido::class, 'id_usuario');
    }

    public function repartidor()
    {
        return $this->hasOne(\App\Models\Repartidor\Repartidor::class, 'id_usuario');
    }

    public function sesiones()
    {
        return $this->hasMany(SesionUsuario::class, 'id_usuario');
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'id_usuario');
    }

    public function hasPermission(string $modulo, string $accion): bool
    {
        foreach ($this->roles as $role) {
            foreach ($role->permisos as $permiso) {
                if ($permiso->modulo->nombre === $modulo && $permiso->accion->nombre === $accion) {
                    return true;
                }
            }
        }
        return false;
    }
}
