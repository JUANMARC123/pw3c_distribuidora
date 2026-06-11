<?php

namespace App\Models\Evidencia;

use Illuminate\Database\Eloquent\Model;

class EvidenciaEntrega extends Model
{
    protected $table = 'evidencias_entrega';
    protected $primaryKey = 'id_evidencia';
    public $timestamps = false;

    protected $fillable = [
        'id_despacho',
        'id_tipo_evidencia',
        'archivo',
        'fecha_registro',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
    ];

    public function despacho()
    {
        return $this->belongsTo(\App\Models\Despacho\Despacho::class, 'id_despacho');
    }

    public function tipoEvidencia()
    {
        return $this->belongsTo(TipoEvidencia::class, 'id_tipo_evidencia');
    }
}
