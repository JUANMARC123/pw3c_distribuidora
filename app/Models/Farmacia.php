<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farmacia extends Model
{
    use HasFactory;

    protected $table = 'farmacias';
    protected $primaryKey = 'id_farmacia';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'email',
        'latitud',
        'longitud',
        'geocerca',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'geocerca' => 'decimal:2',
    ];

    public function contactos()
    {
        return $this->hasMany(ContactoFarmacia::class, 'id_farmacia');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_farmacia');
    }
}
