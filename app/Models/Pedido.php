<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    /**
     * Único origen de verdad del estado de un pedido.
     *
     * `pedidos.estado` es un ENUM de texto (ver 2026_07_29_163000_remove_estados_
     * aprobado_preparacion.php). El front-end trabaja con ids numéricos porque la
     * tabla `estados_pedido` ya no existe, así que el id se deriva del orden de
     * este mapa. Añadir estados nuevos AL FINAL para no renumerar los existentes.
     *
     * clave => nombre visible
     */
    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'en_ruta'   => 'En ruta',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ];

    protected $table = 'pedidos';
    protected $primaryKey = 'id_pedido';

    protected $appends = ['id_estado_pedido'];

    protected $fillable = [
        'id_farmacia',
        'id_usuario',
        'estado',
        'fecha_pedido',
        'observaciones',
        'subtotal',
        'descuento',
        'total',
        'archivo_pedido',
    ];

    protected $casts = [
        'fecha_pedido' => 'datetime',
        'estado' => 'string',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Id numérico del estado, derivado de self::ESTADOS.
     * Devuelve null si el valor almacenado no está en el catálogo.
     */
    public function getIdEstadoPedidoAttribute(): ?int
    {
        return self::estadoToId($this->getAttribute('estado'));
    }

    /** Catálogo en la forma que consume el front-end. */
    public static function estadosCatalogo(): array
    {
        $catalogo = [];

        foreach (self::ESTADOS as $clave => $nombre) {
            $catalogo[] = [
                'id_estado_pedido' => self::estadoToId($clave),
                'clave'            => $clave,
                'nombre_estado'    => $nombre,
            ];
        }

        return $catalogo;
    }

    /** 'en_ruta' => 2 */
    public static function estadoToId(?string $clave): ?int
    {
        if ($clave === null || $clave === '') {
            return null;
        }

        $pos = array_search($clave, array_keys(self::ESTADOS), true);

        return $pos === false ? null : $pos + 1;
    }

    /** 2 => 'en_ruta' */
    public static function estadoFromId($id): ?string
    {
        if ($id === null || $id === '') {
            return null;
        }

        $claves = array_keys(self::ESTADOS);
        $index  = (int) $id - 1;

        return isset($claves[$index]) ? $claves[$index] : null;
    }

    /** Normaliza la entrada de cambiar-estado: acepta clave de texto o id numérico. */
    public static function resolverEstado($estado = null, $idEstadoPedido = null): ?string
    {
        if (is_string($estado) && isset(self::ESTADOS[$estado])) {
            return $estado;
        }

        return self::estadoFromId($idEstadoPedido);
    }

    public function farmacia()
    {
        return $this->belongsTo(Farmacia::class, 'id_farmacia');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function despacho()
    {
        return $this->hasOne(Despacho::class, 'id_pedido');
    }

    public function hojaRutaDetalles()
    {
        return $this->hasMany(HojaRutaDetalle::class, 'id_pedido');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido');
    }
}
