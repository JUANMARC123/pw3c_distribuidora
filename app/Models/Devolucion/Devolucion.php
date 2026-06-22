<?php

namespace App\Models\Devolucion;

use Illuminate\Database\Eloquent\Model;

class Devolucion extends Model
{
    protected $table = 'devoluciones';
    protected $primaryKey = 'id_devolucion';
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'id_usuario',
        'id_tipo_devolucion',
        'id_estado_devolucion',
        'motivo',
        'fecha_devolucion',
    ];

    protected $casts = [
        'fecha_devolucion' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(\App\Models\Pedido\Pedido::class, 'id_pedido');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\Seguridad\Usuario::class, 'id_usuario');
    }

    public function tipoDevolucion()
    {
        return $this->belongsTo(TipoDevolucion::class, 'id_tipo_devolucion');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoDevolucion::class, 'id_estado_devolucion');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleDevolucion::class, 'id_devolucion');
    }

    public function historiales()
    {
        return $this->hasMany(HistorialEstadoDevolucion::class, 'id_devolucion');
    }
}
