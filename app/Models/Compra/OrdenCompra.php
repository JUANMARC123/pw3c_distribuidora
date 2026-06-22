<?php

namespace App\Models\Compra;

use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    protected $table = 'ordenes_compra';
    protected $primaryKey = 'id_orden_compra';

    protected $fillable = [
        'codigo_orden', 'id_proveedor', 'id_usuario', 'id_estado_orden_compra',
        'fecha_orden', 'fecha_estimada_recibido', 'observaciones',
    ];

    protected $casts = [
        'fecha_orden' => 'date',
        'fecha_estimada_recibido' => 'date',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\Seguridad\Usuario::class, 'id_usuario');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoOrdenCompra::class, 'id_estado_orden_compra');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'id_orden_compra');
    }
}
