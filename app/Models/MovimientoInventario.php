<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'inventario_id',
        'despacho_id',
        'user_id',
        'tipo_movimiento',
        'cantidad',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function inventario()
    {
        return $this->belongsTo(Inventario::class);
    }

    public function despacho()
    {
        return $this->belongsTo(Despacho::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
