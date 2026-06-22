<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // === CHECK CONSTRAINTS ===

        $checks = [
            'ALTER TABLE capacidades ADD CONSTRAINT capacidades_capacidad_kg_check CHECK (capacidad_kg >= 0)',
            'ALTER TABLE ruta_paradas ADD CONSTRAINT ruta_paradas_orden_parada_check CHECK (orden_parada >= 1)',
            'ALTER TABLE lotes ADD CONSTRAINT lotes_fecha_vencimiento_check CHECK (fecha_vencimiento > fecha_fabricacion)',
            'ALTER TABLE promociones ADD CONSTRAINT promociones_fecha_fin_check CHECK (fecha_fin IS NULL OR fecha_fin > fecha_inicio)',
            'ALTER TABLE promociones ADD CONSTRAINT promociones_descuento_porcentual_check CHECK (es_porcentual = 0 OR (es_porcentual = 1 AND descuento >= 0 AND descuento <= 100))',
            'ALTER TABLE farmacias ADD CONSTRAINT farmacias_horario_check CHECK (horario_apertura IS NULL OR horario_cierre IS NULL OR horario_cierre > horario_apertura)',
        ];

        foreach ($checks as $sql) {
            try {
                DB::statement($sql);
            } catch (\Exception $e) {
                // constraint may already exist or column may be null
            }
        }

        // === UNIQUE INDEXES ===

        $uniques = [
            'ALTER TABLE detalle_pedido ADD UNIQUE INDEX detalle_pedido_producto_unique (id_pedido, id_producto)',
            'ALTER TABLE control_rutas ADD UNIQUE INDEX control_rutas_repartidor_fecha_unique (id_repartidor, fecha_ruta)',
            'ALTER TABLE control_rutas ADD UNIQUE INDEX control_rutas_vehiculo_fecha_unique (id_vehiculo, fecha_ruta)',
        ];

        foreach ($uniques as $sql) {
            try {
                DB::statement($sql);
            } catch (\Exception $e) {
                // index may already exist
            }
        }
    }

    public function down()
    {
        $indexes = [
            'capacidades_capacidad_kg_check',
            'ruta_paradas_orden_parada_check',
            'lotes_fecha_vencimiento_check',
            'promociones_fecha_fin_check',
            'promociones_descuento_porcentual_check',
            'farmacias_horario_check',
            'detalle_pedido_producto_unique',
            'control_rutas_repartidor_fecha_unique',
            'control_rutas_vehiculo_fecha_unique',
        ];

        foreach ($indexes as $name) {
            try {
                $table = explode('_', $name)[0] . '_' . explode('_', $name)[1];
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
            } catch (\Exception $e) {
            }
        }
    }
};
