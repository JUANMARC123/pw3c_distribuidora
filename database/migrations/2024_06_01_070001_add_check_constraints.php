<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $checks = [
            ['farmacias', 'farmacias_latitud_check', 'latitud >= -90 AND latitud <= 90'],
            ['farmacias', 'farmacias_longitud_check', 'longitud >= -180 AND longitud <= 180'],
            ['productos', 'productos_precio_check', 'precio_unitario >= 0'],
            ['detalle_pedido', 'detalle_pedido_cantidad_check', 'cantidad > 0'],
            ['detalle_pedido', 'detalle_pedido_precio_check', 'precio_unitario >= 0'],
            ['detalles_compra', 'detalles_compra_cantidad_check', 'cantidad > 0'],
            ['detalles_compra', 'detalles_compra_precio_check', 'precio_unitario >= 0'],
            ['detalles_devolucion', 'detalles_devolucion_cantidad_check', 'cantidad > 0'],
            ['detalles_devolucion', 'detalles_devolucion_precio_check', 'precio_unitario >= 0'],
            ['promociones', 'promociones_descuento_check', 'descuento >= 0'],
            ['inventario', 'inventario_stock_actual_check', 'stock_actual >= 0'],
            ['inventario', 'inventario_stock_minimo_check', 'stock_minimo >= 0'],
        ];
        foreach ($checks as [$table, $name, $condition]) {
            try {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$name}");
            } catch (\Exception $e) {}
            try {
                DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$condition})");
            } catch (\Exception $e) {}
        }
    }

    public function down()
    {
        $checks = [
            ['farmacias', 'farmacias_latitud_check'],
            ['farmacias', 'farmacias_longitud_check'],
            ['productos', 'productos_precio_check'],
            ['detalle_pedido', 'detalle_pedido_cantidad_check'],
            ['detalle_pedido', 'detalle_pedido_precio_check'],
            ['detalles_compra', 'detalles_compra_cantidad_check'],
            ['detalles_compra', 'detalles_compra_precio_check'],
            ['detalles_devolucion', 'detalles_devolucion_cantidad_check'],
            ['detalles_devolucion', 'detalles_devolucion_precio_check'],
            ['promociones', 'promociones_descuento_check'],
            ['inventario', 'inventario_stock_actual_check'],
            ['inventario', 'inventario_stock_minimo_check'],
        ];
        foreach ($checks as [$table, $name]) {
            try {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$name}");
            } catch (\Exception $e) {}
        }
    }
};
