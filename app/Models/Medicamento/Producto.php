<?php

namespace App\Models\Medicamento;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'codigo_producto',
        'nombre_producto',
        'descripcion',
        'id_categoria',
        'id_laboratorio',
        'id_presentacion',
        'id_unidad_medida',
        'concentracion',
        'precio_unitario',
        'requiere_receta',
        'activo',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'requiere_receta' => 'boolean',
        'activo' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function laboratorio()
    {
        return $this->belongsTo(Laboratorio::class, 'id_laboratorio');
    }

    public function presentacion()
    {
        return $this->belongsTo(Presentacion::class, 'id_presentacion');
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'id_unidad_medida');
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class, 'id_producto');
    }
}
