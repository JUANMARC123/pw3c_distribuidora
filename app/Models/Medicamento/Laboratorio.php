<?php

namespace App\Models\Medicamento;

use Illuminate\Database\Eloquent\Model;

class Laboratorio extends Model
{
    protected $table = 'laboratorios';
    protected $primaryKey = 'id_laboratorio';
    public $timestamps = false;

    protected $fillable = ['nombre_laboratorio', 'telefono', 'direccion'];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_laboratorio');
    }
}
