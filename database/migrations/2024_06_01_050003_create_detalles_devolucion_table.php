<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('detalles_devolucion', function (Blueprint $table) {
            $table->id('id_detalle_devolucion');
            $table->unsignedBigInteger('id_devolucion');
            $table->unsignedBigInteger('id_producto');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->text('motivo_detalle')->nullable();

            $table->foreign('id_devolucion')
                  ->references('id_devolucion')
                  ->on('devoluciones')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('id_producto')
                  ->references('id_producto')
                  ->on('productos')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('detalles_devolucion');
    }
};
