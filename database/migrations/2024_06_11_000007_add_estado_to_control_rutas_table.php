<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('control_rutas', function (Blueprint $table) {
            if (!Schema::hasColumn('control_rutas', 'id_estado_ruta')) {
                $table->unsignedTinyInteger('id_estado_ruta')->default(1)->after('id_vehiculo');
            }

            $table->foreign('id_estado_ruta')
                  ->references('id_estado_ruta')
                  ->on('estados_ruta')
                  ->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::table('control_rutas', function (Blueprint $table) {
            $table->dropForeign(['id_estado_ruta']);

            if (Schema::hasColumn('control_rutas', 'id_estado_ruta')) {
                $table->dropColumn('id_estado_ruta');
            }
        });
    }
};
