<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Despacho extends Model
{
    use HasFactory;

    protected $table = 'despachos';
    protected $primaryKey = 'id_despacho';

    protected $fillable = [
        'id_pedido',
        'id_detalle_hoja_ruta',
        'fecha_hora_despacho',
        'estado_despacho',
        'monto_recibido',
    ];

    protected $casts = [
        'fecha_hora_despacho' => 'datetime',
        'estado_despacho' => 'string',
        'monto_recibido' => 'decimal:2',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function hojaRutaDetalle()
    {
        return $this->belongsTo(HojaRutaDetalle::class, 'id_detalle_hoja_ruta');
    }

    public function evidencias()
    {
        return $this->hasMany(EvidenciaEntrega::class, 'id_despacho');
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class, 'id_despacho');
    }
}
