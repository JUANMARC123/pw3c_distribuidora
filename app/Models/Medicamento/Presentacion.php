<?php

namespace App\Models\Medicamento;

use Illuminate\Database\Eloquent\Model;

class Presentacion extends Model
{
    protected $table = 'presentaciones';
    protected $primaryKey = 'id_presentacion';
    public $timestamps = false;

    protected $fillable = ['nombre_presentacion'];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_presentacion');
    }
}
