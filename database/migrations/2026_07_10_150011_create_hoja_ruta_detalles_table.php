<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hoja_ruta_detalles', function (Blueprint $table) {
            $table->bigIncrements('id_detalle');
            $table->unsignedBigInteger('id_hoja_ruta');
            $table->unsignedBigInteger('id_pedido');
            $table->integer('orden_visita');
            $table->time('hora_llegada')->nullable();
            $table->time('hora_salida')->nullable();
            $table->timestamps();

            $table->foreign('id_hoja_ruta')
                ->references('id_hoja_ruta')->on('hojas_ruta')
                ->onDelete('cascade');

            $table->foreign('id_pedido')
                ->references('id_pedido')->on('pedidos')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hoja_ruta_detalles');
    }
};
