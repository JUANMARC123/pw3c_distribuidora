<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('caja_repartidor', function (Blueprint $table) {
            $table->bigIncrements('id_caja');
            $table->unsignedBigInteger('id_hoja_ruta')->unique();
            $table->unsignedBigInteger('id_repartidor');
            $table->date('fecha');
            $table->decimal('ingresos', 10, 2)->default(0);
            $table->decimal('monto_final', 10, 2)->nullable();
            $table->decimal('monto_inicial', 10, 2)->default(0);
            $table->enum('estado_caja', ['abierta', 'cerrada', 'auditada'])->default('abierta');
            $table->timestamps();

            $table->foreign('id_hoja_ruta')
                ->references('id_hoja_ruta')->on('hojas_ruta')
                ->onDelete('cascade');

            $table->foreign('id_repartidor')
                ->references('id_repartidor')->on('repartidores')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('caja_repartidor');
    }
};
