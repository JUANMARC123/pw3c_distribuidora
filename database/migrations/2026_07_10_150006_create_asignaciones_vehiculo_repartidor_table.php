<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asignaciones_vehiculo_repartidor', function (Blueprint $table) {
            $table->bigIncrements('id_asignacion');
            $table->unsignedBigInteger('id_repartidor');
            $table->unsignedBigInteger('id_vehiculo');
            $table->timestamp('fecha_asignacion')->useCurrent();
            $table->timestamp('fecha_devolucion')->nullable();
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
        Schema::dropIfExists('asignaciones_vehiculo_repartidor');
    }
};
