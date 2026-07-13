<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajaRepartidor extends Model
{
    use HasFactory;

    protected $table = 'caja_repartidor';
    protected $primaryKey = 'id_caja';

    protected $fillable = [
        'id_hoja_ruta',
        'id_repartidor',
        'fecha',
        'ingresos',
        'monto_final',
        'monto_inicial',
        'estado_caja',
    ];

    protected $casts = [
        'fecha' => 'date',
        'ingresos' => 'decimal:2',
        'monto_final' => 'decimal:2',
        'monto_inicial' => 'decimal:2',
        'estado_caja' => 'string',
    ];

    public function hojaRuta()
    {
        return $this->belongsTo(HojaRuta::class, 'id_hoja_ruta');
    }

    public function repartidor()
    {
        return $this->belongsTo(Repartidor::class, 'id_repartidor');
    }

    public function gastos()
    {
        return $this->hasMany(GastoOperativo::class, 'id_caja');
    }
}
