<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('control_rutas', function (Blueprint $table) {
            $table->id('id_control_ruta');
            $table->unsignedSmallInteger('id_ruta');
            $table->date('fecha_ruta');
            $table->time('hora_salida');
            $table->time('hora_llegada_real')->nullable();
            $table->unsignedBigInteger('id_repartidor');
            $table->unsignedBigInteger('id_vehiculo');

            $table->foreign('id_ruta')
                  ->references('id_ruta')
                  ->on('rutas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_repartidor')
                  ->references('id_repartidor')
                  ->on('repartidores')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_vehiculo')
                  ->references('id_vehiculo')
                  ->on('vehiculos')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->unique(['id_ruta', 'fecha_ruta']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('control_rutas');
    }
};
