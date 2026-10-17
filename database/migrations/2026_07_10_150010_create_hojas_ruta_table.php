<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hojas_ruta', function (Blueprint $table) {
            $table->bigIncrements('id_hoja_ruta');
            $table->date('fecha');
            $table->time('hora_salida')->nullable();
            $table->time('hora_llegada')->nullable();
            $table->unsignedBigInteger('id_repartidor');
            $table->unsignedBigInteger('id_vehiculo');
            $table->enum('estado_ruta', ['planificado', 'en_progreso', 'completado', 'cancelado'])->default('planificado');
            $table->timestamps();

            $table->foreign('id_repartidor')
                ->references('id_repartidor')->on('repartidores')
                ->onDelete('cascade');

            $table->foreign('id_vehiculo')
                ->references('id_vehiculo')->on('vehiculos')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hojas_ruta');
    }
};
