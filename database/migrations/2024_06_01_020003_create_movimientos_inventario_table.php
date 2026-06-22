<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id('id_movimiento');
            $table->unsignedBigInteger('id_inventario');
            $table->tinyInteger('id_tipo_movimiento')->unsigned();
            $table->unsignedBigInteger('id_usuario');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('stock_anterior', 10, 2);
            $table->decimal('stock_posterior', 10, 2);
            $table->string('referencia', 100)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_inventario')
                  ->references('id_inventario')
                  ->on('inventario')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_tipo_movimiento')
                  ->references('id_tipo_movimiento')
                  ->on('tipos_movimiento')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_usuario')
                  ->references('id_usuario')
                  ->on('usuarios')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
