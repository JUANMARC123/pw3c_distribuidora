<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id('id_devolucion');
            $table->unsignedBigInteger('id_pedido');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedTinyInteger('id_tipo_devolucion');
            $table->unsignedTinyInteger('id_estado_devolucion');
            $table->text('motivo')->nullable();
            $table->timestamp('fecha_devolucion')->useCurrent();

            $table->foreign('id_pedido')
                  ->references('id_pedido')
                  ->on('pedidos')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_usuario')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_tipo_devolucion')
                  ->references('id_tipo_devolucion')
                  ->on('tipos_devolucion')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_estado_devolucion')
                  ->references('id_estado_devolucion')
                  ->on('estados_devolucion')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });

        Schema::create('historial_estado_devolucion', function (Blueprint $table) {
            $table->id('id_historial');
            $table->unsignedBigInteger('id_devolucion');
            $table->unsignedTinyInteger('id_estado_devolucion');
            $table->timestamp('fecha_inicio')->useCurrent();
            $table->timestamp('fecha_fin')->nullable();

            $table->foreign('id_devolucion')
                  ->references('id_devolucion')
                  ->on('devoluciones')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('id_estado_devolucion')
                  ->references('id_estado_devolucion')
                  ->on('estados_devolucion')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->unique(['id_devolucion', 'fecha_inicio']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('historial_estado_devolucion');
        Schema::dropIfExists('devoluciones');
    }
};
