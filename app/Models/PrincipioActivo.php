<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrincipioActivo extends Model
{
    use HasFactory;

    protected $table = 'principios_activos';

    protected $fillable = [
        'nombre',
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'productos_principios')
            ->withPivot('cantidad');
    }
}
