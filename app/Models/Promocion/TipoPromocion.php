<?php

namespace App\Models\Promocion;

use Illuminate\Database\Eloquent\Model;

class TipoPromocion extends Model
{
    protected $table = 'tipos_promocion';
    protected $primaryKey = 'id_tipo_promocion';
    public $timestamps = false;

    protected $fillable = ['nombre_tipo'];
}
