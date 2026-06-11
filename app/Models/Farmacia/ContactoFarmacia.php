<?php

namespace App\Models\Farmacia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactoFarmacia extends Model
{
    use HasFactory;
    protected $table = 'contactos_farmacia';
    protected $primaryKey = 'id_contacto';
    public $timestamps = false;

    protected $fillable = [
        'id_farmacia',
        'nombre_contacto',
        'id_cargo',
        'telefono',
        'email',
    ];

    public function farmacia()
    {
        return $this->belongsTo(Farmacia::class, 'id_farmacia');
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'id_cargo');
    }
}
