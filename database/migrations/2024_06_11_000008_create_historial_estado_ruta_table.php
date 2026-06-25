<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('historial_estado_ruta', function (Blueprint $table) {
            $table->bigIncrements('id_historial');
            $table->unsignedBigInteger('id_control_ruta');
            $table->unsignedTinyInteger('id_estado_ruta');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();

            $table->foreign('id_control_ruta')
                  ->references('id_control_ruta')
                  ->on('control_rutas')
                  ->onDelete('cascade');

            $table->foreign('id_estado_ruta')
                  ->references('id_estado_ruta')
                  ->on('estados_ruta')
                  ->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('historial_estado_ruta');
    }
};
