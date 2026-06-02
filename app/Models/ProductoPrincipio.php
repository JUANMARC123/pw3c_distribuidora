<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoPrincipio extends Model
{
    use HasFactory;

    protected $table = 'productos_principios';

    protected $fillable = [
        'producto_id',
        'principio_activo_id',
        'cantidad',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function principioActivo()
    {
        return $this->belongsTo(PrincipioActivo::class);
    }
}
