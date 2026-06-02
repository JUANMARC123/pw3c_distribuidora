<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactoFarmacia extends Model
{
    use HasFactory;

    protected $table = 'contactos_farmacia';

    protected $fillable = [
        'farmacia_id',
        'nombre',
        'cargo',
        'telefono',
        'correo',
    ];

    public function farmacia()
    {
        return $this->belongsTo(Farmacia::class);
    }
}
