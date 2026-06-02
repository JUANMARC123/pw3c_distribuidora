<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleDespacho extends Model
{
    use HasFactory;

    protected $table = 'detalle_despachos';

    protected $fillable = [
        'despacho_id',
        'inventario_id',
        'cantidad',
    ];

    public function despacho()
    {
        return $this->belongsTo(Despacho::class);
    }

    public function inventario()
    {
        return $this->belongsTo(Inventario::class);
    }
}
