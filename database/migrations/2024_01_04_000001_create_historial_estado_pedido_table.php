<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('historial_estado_pedido', function (Blueprint $table) {
            $table->id('id_historial');
            $table->unsignedBigInteger('id_pedido');
            $table->tinyInteger('id_estado_pedido')->unsigned();
            $table->timestamp('fecha_inicio')->useCurrent();
            $table->timestamp('fecha_fin')->nullable();

            $table->foreign('id_pedido')
                  ->references('id_pedido')
                  ->on('pedidos')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('id_estado_pedido')
                  ->references('id_estado_pedido')
                  ->on('estados_pedido')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->unique(['id_pedido', 'fecha_inicio']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('historial_estado_pedido');
    }
};
