<?php

namespace App\Models\Promocion;

use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    protected $table = 'promociones';
    protected $primaryKey = 'id_promocion';
    public $timestamps = false;

    protected $fillable = [
        'nombre_promocion',
        'descripcion',
        'id_tipo_promocion',
        'descuento',
        'es_porcentual',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'id_usuario',
    ];

    protected $casts = [
        'descuento' => 'decimal:2',
        'es_porcentual' => 'boolean',
        'activo' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function tipoPromocion()
    {
        return $this->belongsTo(TipoPromocion::class, 'id_tipo_promocion');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\Seguridad\Usuario::class, 'id_usuario');
    }

    public function productos()
    {
        return $this->hasMany(ProductoPromocion::class, 'id_promocion');
    }
}
