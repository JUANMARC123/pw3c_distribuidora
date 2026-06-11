<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ruta_paradas', function (Blueprint $table) {
            $table->id('id_parada');
            $table->unsignedSmallInteger('id_ruta');
            $table->unsignedBigInteger('id_farmacia');
            $table->smallInteger('orden_parada');
            $table->time('hora_estimada');

            $table->foreign('id_ruta')
                  ->references('id_ruta')
                  ->on('rutas')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('id_farmacia')
                  ->references('id_farmacia')
                  ->on('farmacias')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->unique(['id_ruta', 'orden_parada']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ruta_paradas');
    }
};
