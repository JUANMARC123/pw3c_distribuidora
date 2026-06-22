<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('estados_venta', function (Blueprint $table) {
            $table->tinyIncrements('id_estado_venta');
            $table->string('nombre_estado', 50)->unique();
        });

        Schema::create('ventas', function (Blueprint $table) {
            $table->id('id_venta');
            $table->unsignedBigInteger('id_pedido');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedTinyInteger('id_estado_venta');
            $table->dateTime('fecha_venta')->useCurrent();
            $table->decimal('total', 10, 2);

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

            $table->foreign('id_estado_venta')
                  ->references('id_estado_venta')
                  ->on('estados_venta')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });

        Schema::create('detalle_venta', function (Blueprint $table) {
            $table->id('id_detalle');
            $table->unsignedBigInteger('id_venta');
            $table->unsignedBigInteger('id_lote');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);

            $table->foreign('id_venta')
                  ->references('id_venta')
                  ->on('ventas')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('id_lote')
                  ->references('id_lote')
                  ->on('lotes')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });

        Schema::create('metodos_pago', function (Blueprint $table) {
            $table->tinyIncrements('id_metodo_pago');
            $table->string('nombre_metodo', 50)->unique();
        });

        Schema::create('pagos', function (Blueprint $table) {
            $table->id('id_pago');
            $table->unsignedBigInteger('id_venta');
            $table->unsignedTinyInteger('id_metodo_pago');
            $table->decimal('monto', 10, 2);
            $table->dateTime('fecha_pago')->useCurrent();
            $table->string('referencia', 100)->nullable();

            $table->foreign('id_venta')
                  ->references('id_venta')
                  ->on('ventas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_metodo_pago')
                  ->references('id_metodo_pago')
                  ->on('metodos_pago')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('metodos_pago');
        Schema::dropIfExists('detalle_venta');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('estados_venta');
    }
};
