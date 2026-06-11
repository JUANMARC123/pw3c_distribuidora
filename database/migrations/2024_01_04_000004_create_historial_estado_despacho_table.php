<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('historial_estado_despacho', function (Blueprint $table) {
            $table->id('id_historial');
            $table->unsignedBigInteger('id_despacho');
            $table->tinyInteger('id_estado_despacho')->unsigned();
            $table->timestamp('fecha_inicio')->useCurrent();
            $table->timestamp('fecha_fin')->nullable();

            $table->foreign('id_despacho')
                  ->references('id_despacho')
                  ->on('despachos')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('id_estado_despacho')
                  ->references('id_estado_despacho')
                  ->on('estados_despacho')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->unique(['id_despacho', 'fecha_inicio']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('historial_estado_despacho');
    }
};
