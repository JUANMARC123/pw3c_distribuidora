<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('planificacion_rutas', function (Blueprint $table) {
            $table->bigIncrements('id_planificacion');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_control_ruta')->nullable();
            $table->string('estado', 20)->default('BORRADOR');
            $table->json('datos_json')->nullable();
            $table->dateTime('fecha_creacion')->useCurrent();
            $table->dateTime('fecha_confirmacion')->nullable();

            $table->foreign('id_usuario')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onDelete('restrict');

            $table->foreign('id_control_ruta')
                  ->references('id_control_ruta')
                  ->on('control_rutas')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('planificacion_rutas');
    }
};
