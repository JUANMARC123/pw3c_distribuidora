<?php

namespace App\Models\Evidencia;

use Illuminate\Database\Eloquent\Model;

class TipoEvidencia extends Model
{
    protected $table = 'tipos_evidencia';
    protected $primaryKey = 'id_tipo_evidencia';
    public $timestamps = false;

    protected $fillable = ['nombre_tipo'];

    public function evidencias()
    {
        return $this->hasMany(EvidenciaEntrega::class, 'id_tipo_evidencia');
    }
}
