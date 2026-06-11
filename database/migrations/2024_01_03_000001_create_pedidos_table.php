<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id('id_pedido');
            $table->unsignedBigInteger('id_farmacia');
            $table->unsignedBigInteger('id_usuario');
            $table->tinyInteger('id_estado_pedido')->unsigned();
            $table->timestamp('fecha_pedido')->useCurrent();
            $table->text('observaciones')->nullable();

            $table->foreign('id_farmacia')
                  ->references('id_farmacia')
                  ->on('farmacias')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_usuario')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_estado_pedido')
                  ->references('id_estado_pedido')
                  ->on('estados_pedido')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
};
