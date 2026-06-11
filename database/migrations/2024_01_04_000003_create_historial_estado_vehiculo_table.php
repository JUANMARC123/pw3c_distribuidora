<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('historial_estado_vehiculo', function (Blueprint $table) {
            $table->id('id_historial');
            $table->unsignedBigInteger('id_vehiculo');
            $table->tinyInteger('id_estado_vehiculo')->unsigned();
            $table->timestamp('fecha_inicio')->useCurrent();
            $table->timestamp('fecha_fin')->nullable();

            $table->foreign('id_vehiculo')
                  ->references('id_vehiculo')
                  ->on('vehiculos')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('id_estado_vehiculo')
                  ->references('id_estado_vehiculo')
                  ->on('estados_vehiculo')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->unique(['id_vehiculo', 'fecha_inicio']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('historial_estado_vehiculo');
    }
};
