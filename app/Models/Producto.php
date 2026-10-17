<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'nombre',
        'precio_unitario',
        'unidad_medida',
        'estado',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'estado' => 'boolean',
    ];

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'id_producto');
    }
}
