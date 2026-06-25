<?php

namespace App\Models\Caja;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastoOperativo extends Model
{
    use HasFactory;
    protected $table = 'gastos_operativos';
    protected $primaryKey = 'id_gasto';
    public $timestamps = false;

    protected $fillable = [
        'id_caja',
        'tipo_gasto',
        'descripcion',
        'monto',
        'comprobante',
        'fecha',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    public function caja()
    {
        return $this->belongsTo(CajaRepartidor::class, 'id_caja');
    }
}
