<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('despachos', function (Blueprint $table) {
            $table->bigIncrements('id_despacho');
            $table->unsignedBigInteger('id_pedido')->unique();
            $table->unsignedBigInteger('id_detalle_hoja_ruta');
            $table->timestamp('fecha_hora_despacho')->useCurrent();
            $table->enum('estado_despacho', ['pendiente', 'entregado', 'incidencia'])->default('pendiente');
            $table->decimal('monto_recibido', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('id_pedido')
                ->references('id_pedido')->on('pedidos')
                ->onDelete('cascade');

            $table->foreign('id_detalle_hoja_ruta')
                ->references('id_detalle')->on('hoja_ruta_detalles')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('despachos');
    }
};
