<?php

namespace App\Models\Compra;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';
    protected $primaryKey = 'id_proveedor';

    protected $fillable = ['nombre_proveedor', 'nit', 'telefono', 'email', 'direccion'];

    public function contactos()
    {
        return $this->hasMany(ContactoProveedor::class, 'id_proveedor');
    }

    public function ordenesCompra()
    {
        return $this->hasMany(OrdenCompra::class, 'id_proveedor');
    }
}
