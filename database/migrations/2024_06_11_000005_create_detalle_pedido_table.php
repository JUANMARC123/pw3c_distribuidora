<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('detalle_pedido', function (Blueprint $table) {
            $table->bigIncrements('id_detalle');
            $table->unsignedBigInteger('id_pedido');
            $table->unsignedBigInteger('id_producto');
            $table->decimal('cantidad_solicitada', 10, 2)->default(0);
            $table->decimal('cantidad_entregada', 10, 2)->default(0);
            $table->decimal('precio_unitario', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->unsignedTinyInteger('id_estado_detalle')->default(1);

            $table->foreign('id_pedido')
                  ->references('id_pedido')
                  ->on('pedidos')
                  ->onDelete('cascade');

            $table->foreign('id_producto')
                  ->references('id_producto')
                  ->on('productos')
                  ->onDelete('restrict');

            $table->foreign('id_estado_detalle')
                  ->references('id_estado_detalle')
                  ->on('estados_detalle')
                  ->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('detalle_pedido');
    }
};
