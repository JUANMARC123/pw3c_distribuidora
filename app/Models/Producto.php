<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'categoria_id',
        'laboratorio_id',
        'codigo',
        'nombre',
        'registro_sanitario',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function laboratorio()
    {
        return $this->belongsTo(Laboratorio::class);
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class);
    }

    public function presentaciones()
    {
        return $this->hasMany(Presentacion::class);
    }

    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }

    public function principiosActivos()
    {
        return $this->belongsToMany(PrincipioActivo::class, 'productos_principios')
            ->withPivot('cantidad');
    }
}
