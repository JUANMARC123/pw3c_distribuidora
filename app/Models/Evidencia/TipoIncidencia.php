<?php

namespace App\Models\Evidencia;

use Illuminate\Database\Eloquent\Model;

class TipoIncidencia extends Model
{
    protected $table = 'tipos_incidencia';
    protected $primaryKey = 'id_tipo_incidencia';
    public $timestamps = false;

    protected $fillable = ['nombre_tipo'];

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class, 'id_tipo_incidencia');
    }
}
