<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->bigIncrements('id_pedido');
            $table->unsignedBigInteger('id_farmacia');
            $table->unsignedBigInteger('id_usuario');
            $table->enum('estado', ['pendiente', 'aprobado', 'entregado', 'cancelado'])->default('pendiente');
            $table->enum('metodo_pago', ['efectivo', 'qr', 'transferencia', 'credito'])->nullable();
            $table->timestamp('fecha_pedido')->useCurrent();
            $table->text('observaciones')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('archivo_pedido')->nullable();
            $table->timestamps();

            $table->foreign('id_farmacia')
                ->references('id_farmacia')->on('farmacias')
                ->onDelete('cascade');

            $table->foreign('id_usuario')
                ->references('id_usuario')->on('usuarios')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
};
