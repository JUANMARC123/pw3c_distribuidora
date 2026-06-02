<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvidenciaEntrega extends Model
{
    use HasFactory;

    protected $table = 'evidencias_entrega';

    protected $fillable = [
        'despacho_id',
        'foto',
        'firma',
        'comentario',
    ];

    public function despacho()
    {
        return $this->belongsTo(Despacho::class);
    }
}
