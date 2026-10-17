<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HojaRutaDetalle extends Model
{
    use HasFactory;

    protected $table = 'hoja_ruta_detalles';
    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'id_hoja_ruta',
        'id_pedido',
        'orden_visita',
        'hora_llegada',
        'hora_salida',
    ];

    protected $casts = [
        'hora_llegada' => 'string',
        'hora_salida' => 'string',
    ];

    public function hojaRuta()
    {
        return $this->belongsTo(HojaRuta::class, 'id_hoja_ruta');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function despachos()
    {
        return $this->hasMany(Despacho::class, 'id_detalle_hoja_ruta');
    }
}
