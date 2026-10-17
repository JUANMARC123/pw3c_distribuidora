<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Amplía el ENUM productos.unidad_medida.
 *
 * La tabla se creó con 6 valores (CAJA, BLISTER, TUBO, FRASCO, AMPOLLA, KG), pero
 * el front-end ofrece 14 unidades (public/js/modules/productos.js → UNIDADES) y
 * createCatalogProductFromDetected() guarda 'UNIDAD' y 'MG'. Sin esta ampliación
 * esos INSERT fallaban contra el ENUM de MySQL.
 */
return new class extends Migration
{
    private const UNIDADES = [
        'UNIDAD', 'CAJA', 'BLISTER', 'TUBO', 'FRASCO', 'AMPOLLA', 'KG',
        'LITRO', 'ML', 'MG', 'G', 'ROLLO', 'PAR', 'PAQUETE',
    ];

    public function up()
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // SQLite no soporta MODIFY COLUMN; el CHECK original ya no aplica
            // porque Laravel lo declara como varchar sin restricción real.
            return;
        }

        $enum = implode("', '", self::UNIDADES);

        DB::statement("ALTER TABLE productos MODIFY COLUMN unidad_medida ENUM('{$enum}') NOT NULL DEFAULT 'CAJA'");
    }

    public function down()
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        $enum = implode("', '", ['CAJA', 'BLISTER', 'TUBO', 'FRASCO', 'AMPOLLA', 'KG']);

        // Las unidades nuevas se reconducen a CAJA antes de recortar el ENUM,
        // o MySQL rechaza el ALTER por los valores fuera de dominio.
        DB::statement("UPDATE productos SET unidad_medida = 'CAJA' WHERE unidad_medida NOT IN ('{$enum}')");
        DB::statement("ALTER TABLE productos MODIFY COLUMN unidad_medida ENUM('{$enum}') NOT NULL DEFAULT 'CAJA'");
    }
};
