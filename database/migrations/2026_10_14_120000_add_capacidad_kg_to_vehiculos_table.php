<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Capacidad de carga del vehículo, en kilogramos.
     *
     * Antes el front exponía una lista de capacidades venida de un catálogo que ya
     * no existe. Se reemplaza por una columna nullable propia del vehículo.
     */
    public function up()
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->unsignedInteger('capacidad_kg')->nullable()->after('modelo');
        });
    }

    public function down()
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropColumn('capacidad_kg');
        });
    }
};
