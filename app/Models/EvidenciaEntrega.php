<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvidenciaEntrega extends Model
{
    use HasFactory;

    protected $table = 'evidencias_entrega';
    protected $primaryKey = 'id_evidencia';

    protected $fillable = [
        'id_despacho',
        'tipo_evidencia',
        'archivo',
    ];

    protected $casts = [
        'tipo_evidencia' => 'string',
    ];

    public function despacho()
    {
        return $this->belongsTo(Despacho::class, 'id_despacho');
    }
}
