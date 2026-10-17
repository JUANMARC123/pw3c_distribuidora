<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajaIngreso extends Model
{
    use HasFactory;

    protected $table = 'caja_ingresos';
    protected $primaryKey = 'id_ingreso';

    protected $fillable = [
        'id_caja',
        'id_hoja_ruta',
        'descripcion',
        'monto',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function caja()
    {
        return $this->belongsTo(CajaRepartidor::class, 'id_caja');
    }

    public function hojaRuta()
    {
        return $this->belongsTo(HojaRuta::class, 'id_hoja_ruta');
    }
}
