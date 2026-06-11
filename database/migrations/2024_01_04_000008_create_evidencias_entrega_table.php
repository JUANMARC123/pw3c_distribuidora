<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('evidencias_entrega', function (Blueprint $table) {
            $table->id('id_evidencia');
            $table->unsignedBigInteger('id_despacho');
            $table->tinyInteger('id_tipo_evidencia')->unsigned();
            $table->string('archivo', 255);
            $table->timestamp('fecha_registro')->useCurrent();

            $table->foreign('id_despacho')
                  ->references('id_despacho')
                  ->on('despachos')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('id_tipo_evidencia')
                  ->references('id_tipo_evidencia')
                  ->on('tipos_evidencia')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('evidencias_entrega');
    }
};
