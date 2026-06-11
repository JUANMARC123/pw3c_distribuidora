<?php

namespace App\Models\Farmacia;

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
    ];

    public function contactos()
    {
        return $this->hasMany(ContactoFarmacia::class, 'id_farmacia');
    }

    public function pedidos()
    {
        return $this->hasMany(\App\Models\Pedido\Pedido::class, 'id_farmacia');
    }

    public function paradas()
    {
        return $this->hasMany(\App\Models\Logistica\RutaParada::class, 'id_farmacia');
    }
}
