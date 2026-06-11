<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditorias';
    protected $primaryKey = 'id_auditoria';
    public $timestamps = false;

    protected $fillable = ['id_usuario', 'id_accion', 'id_tabla', 'registro_id', 'fecha_hora'];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function accion()
    {
        return $this->belongsTo(Accion::class, 'id_accion');
    }

    public function tabla()
    {
        return $this->belongsTo(TablaSistema::class, 'id_tabla');
    }
}
