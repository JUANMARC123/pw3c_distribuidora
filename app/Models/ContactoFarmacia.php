<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactoFarmacia extends Model
{
    use HasFactory;

    protected $table = 'contactos_farmacia';
    protected $primaryKey = 'id_contacto';

    protected $fillable = [
        'id_farmacia',
        'nombre_contacto',
        'cargo',
        'telefono',
        'email',
    ];

    public function farmacia()
    {
        return $this->belongsTo(Farmacia::class, 'id_farmacia');
    }
}
