<?php

namespace App\Models\Compra;

use Illuminate\Database\Eloquent\Model;

class ContactoProveedor extends Model
{
    protected $table = 'contactos_proveedor';
    protected $primaryKey = 'id_contacto_proveedor';
    public $timestamps = false;

    protected $fillable = ['id_proveedor', 'nombre_contacto', 'cargo', 'telefono', 'email'];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }
}
