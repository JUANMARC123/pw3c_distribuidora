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
            $table->integer('cantidad_solicitada');
            $table->integer('cantidad_entregada')->default(0);
            $table->integer('cantidad_rechazada')->default(0);
            $table->string('motivo_rechazo')->nullable();
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('id_pedido')
                ->references('id_pedido')->on('pedidos')
                ->onDelete('cascade');

            $table->foreign('id_producto')
                ->references('id_producto')->on('productos')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('detalle_pedido');
    }
};
