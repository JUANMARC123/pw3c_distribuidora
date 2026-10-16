<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Único origen de verdad del estado del usuario.
     * Ver migración 2026_07_10_150001_create_usuarios_table.php
     */
    public const ESTADOS = [
        'activo'    => 'Activo',
        'bloqueado' => 'Bloqueado',
    ];

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'password_hash',
        'telefono',
        'estado',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'estado' => 'string',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function repartidor()
    {
        return $this->hasOne(Repartidor::class, 'id_usuario');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_usuario');
    }

    /** Catálogo de estados en la forma que consume el front-end. */
    public static function estadosCatalogo(): array
    {
        $catalogo = [];
        foreach (array_keys(self::ESTADOS) as $i => $clave) {
            $catalogo[] = [
                'id_estado_usuario' => $i + 1,
                'clave'             => $clave,
                'nombre_estado'     => self::ESTADOS[$clave],
            ];
        }

        return $catalogo;
    }
}
