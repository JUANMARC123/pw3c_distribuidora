<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('despachos', function (Blueprint $table) {
            $table->id('id_despacho');
            $table->unsignedBigInteger('id_pedido');
            $table->unsignedBigInteger('id_parada');
            $table->unsignedBigInteger('id_control_ruta');
            $table->timestamp('fecha_hora_despacho')->useCurrent();
            $table->tinyInteger('id_estado_despacho')->unsigned();

            $table->foreign('id_pedido')
                  ->references('id_pedido')
                  ->on('pedidos')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_parada')
                  ->references('id_parada')
                  ->on('ruta_paradas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_control_ruta')
                  ->references('id_control_ruta')
                  ->on('control_rutas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_estado_despacho')
                  ->references('id_estado_despacho')
                  ->on('estados_despacho')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('despachos');
    }
};
