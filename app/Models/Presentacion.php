<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presentacion extends Model
{
    use HasFactory;

    protected $table = 'presentaciones';

    protected $fillable = [
        'producto_id',
        'forma_farmaceutica_id',
        'descripcion',
        'contenido',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function formaFarmaceutica()
    {
        return $this->belongsTo(FormaFarmaceutica::class);
    }
}
