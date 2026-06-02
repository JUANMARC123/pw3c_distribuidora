<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Farmacia extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'nit',
        'categoria_id',
        'logo',
        'telefono',
        'correo',
        'whatsapp',
        'direccion',
        'latitud',
        'longitud',
        'es_24_horas',
        'estado',
    ];

    protected $casts = [
        'es_24_horas' => 'boolean',
        'estado' => 'boolean',
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function contactos()
    {
        return $this->hasMany(ContactoFarmacia::class);
    }

    public function despachos()
    {
        return $this->hasMany(Despacho::class);
    }

    public function rutaParadas()
    {
        return $this->hasMany(RutaParada::class);
    }
}
