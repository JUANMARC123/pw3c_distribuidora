<?php

namespace App\Models\Medicamento;

use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    protected $table = 'unidades_medida';
    protected $primaryKey = 'id_unidad_medida';
    public $timestamps = false;

    protected $fillable = ['nombre_unidad'];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_unidad_medida');
    }
}
