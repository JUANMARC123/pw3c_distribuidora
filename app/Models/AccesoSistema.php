<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccesoSistema extends Model
{
    use HasFactory;

    protected $table = 'accesos_sistema';

    protected $fillable = [
        'user_id',
        'modulo',
        'accion',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
