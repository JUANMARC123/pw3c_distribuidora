<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('farmacias', function (Blueprint $table) {
            if (!Schema::hasColumn('farmacias', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('zona');
            }
            if (!Schema::hasColumn('farmacias', 'es_24_horas')) {
                $table->boolean('es_24_horas')->default(false)->after('descripcion');
            }
            if (!Schema::hasColumn('farmacias', 'horario_apertura')) {
                $table->time('horario_apertura')->nullable()->after('es_24_horas');
            }
            if (!Schema::hasColumn('farmacias', 'horario_cierre')) {
                $table->time('horario_cierre')->nullable()->after('horario_apertura');
            }
            if (!Schema::hasColumn('farmacias', 'fecha_verificacion')) {
                $table->dateTime('fecha_verificacion')->nullable()->after('horario_cierre');
            }
        });
    }

    public function down()
    {
        Schema::table('farmacias', function (Blueprint $table) {
            $columns = ['descripcion', 'es_24_horas', 'horario_apertura', 'horario_cierre', 'fecha_verificacion'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('farmacias', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
