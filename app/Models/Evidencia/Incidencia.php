<?php

namespace App\Models\Evidencia;

use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    protected $table = 'incidencias';
    protected $primaryKey = 'id_incidencia';
    public $timestamps = false;

    protected $fillable = [
        'id_despacho',
        'id_tipo_incidencia',
        'descripcion',
        'fecha_incidencia',
    ];

    protected $casts = [
        'fecha_incidencia' => 'datetime',
    ];

    public function despacho()
    {
        return $this->belongsTo(\App\Models\Despacho\Despacho::class, 'id_despacho');
    }

    public function tipoIncidencia()
    {
        return $this->belongsTo(TipoIncidencia::class, 'id_tipo_incidencia');
    }
}
