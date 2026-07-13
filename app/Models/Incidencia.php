<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    use HasFactory;

    protected $table = 'incidencias';
    protected $primaryKey = 'id_incidencia';

    protected $fillable = [
        'id_despacho',
        'tipo_incidencia',
        'descripcion',
        'archivo',
    ];

    protected $casts = [
        'tipo_incidencia' => 'string',
    ];

    public function despacho()
    {
        return $this->belongsTo(Despacho::class, 'id_despacho');
    }
}
