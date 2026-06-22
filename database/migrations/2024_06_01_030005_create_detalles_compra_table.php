<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('detalles_compra', function (Blueprint $table) {
            $table->id('id_detalle_compra');
            $table->unsignedBigInteger('id_orden_compra');
            $table->unsignedBigInteger('id_producto');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);

            $table->foreign('id_orden_compra')
                  ->references('id_orden_compra')
                  ->on('ordenes_compra')
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
        Schema::dropIfExists('detalles_compra');
    }
};
