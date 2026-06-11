<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;

class TablaSistema extends Model
{
    protected $table = 'tablas_sistema';
    protected $primaryKey = 'id_tabla';
    public $timestamps = false;

    protected $fillable = ['nombre'];

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'id_tabla');
    }
}
