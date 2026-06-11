<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id('id_incidencia');
            $table->unsignedBigInteger('id_despacho');
            $table->tinyInteger('id_tipo_incidencia')->unsigned();
            $table->text('descripcion');
            $table->timestamp('fecha_incidencia')->useCurrent();

            $table->foreign('id_despacho')
                  ->references('id_despacho')
                  ->on('despachos')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('id_tipo_incidencia')
                  ->references('id_tipo_incidencia')
                  ->on('tipos_incidencia')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('incidencias');
    }
};
