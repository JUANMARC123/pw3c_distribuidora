<?php

namespace App\Models\Caja;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajaRepartidor extends Model
{
    use HasFactory;
    protected $table = 'caja_repartidor';
    protected $primaryKey = 'id_caja';
    public $timestamps = false;

    protected $fillable = [
        'id_control_ruta',
        'id_repartidor',
        'fecha',
        'monto_inicial',
        'ingresos',
        'gastos',
        'monto_final',
        'id_estado_caja',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto_inicial' => 'decimal:2',
        'ingresos' => 'decimal:2',
        'gastos' => 'decimal:2',
        'monto_final' => 'decimal:2',
    ];

    public function repartidor()
    {
        return $this->belongsTo(\App\Models\Repartidor\Repartidor::class, 'id_repartidor');
    }

    public function controlRuta()
    {
        return $this->belongsTo(\App\Models\Logistica\ControlRuta::class, 'id_control_ruta');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoCaja::class, 'id_estado_caja');
    }

    public function gastos()
    {
        return $this->hasMany(GastoOperativo::class, 'id_caja');
    }
}
