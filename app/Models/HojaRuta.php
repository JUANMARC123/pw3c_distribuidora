<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HojaRuta extends Model
{
    use HasFactory;

    protected $table = 'hojas_ruta';
    protected $primaryKey = 'id_hoja_ruta';

    protected $fillable = [
        'fecha',
        'hora_salida',
        'hora_llegada',
        'id_repartidor',
        'id_vehiculo',
        'id_caja',
        'cobrado_efectivo',
        'cobrado_qr',
        'estado_ruta',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_salida' => 'string',
        'hora_llegada' => 'string',
        'cobrado_efectivo' => 'decimal:2',
        'cobrado_qr' => 'decimal:2',
        'estado_ruta' => 'string',
    ];

    public function repartidor()
    {
        return $this->belongsTo(Repartidor::class, 'id_repartidor');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'id_vehiculo');
    }

    public function detalles()
    {
        return $this->hasMany(HojaRutaDetalle::class, 'id_hoja_ruta')->orderBy('orden_visita');
    }

    public function caja()
    {
        return $this->belongsTo(CajaRepartidor::class, 'id_caja');
    }

    /**
     * Asegura que la hoja de ruta esté vinculada a una caja abierta
     * del mismo repartidor y fecha. Si no existe caja, la crea automáticamente.
     */
    public function asegurarVinculoCaja(): void
    {
        if ($this->id_caja) {
            return;
        }

        $cajaAbierta = CajaRepartidor::where('id_repartidor', $this->id_repartidor)
            ->where('fecha', $this->fecha->format('Y-m-d'))
            ->where('estado_caja', 'abierta')
            ->first();

        if (!$cajaAbierta) {
            $cajaAbierta = CajaRepartidor::create([
                'id_repartidor' => $this->id_repartidor,
                'fecha' => $this->fecha->format('Y-m-d'),
                'monto_inicial' => 0,
                'ingresos' => 0,
                'monto_final' => null,
                'estado_caja' => 'abierta',
            ]);
        }

        $this->update(['id_caja' => $cajaAbierta->id_caja]);
    }
}
