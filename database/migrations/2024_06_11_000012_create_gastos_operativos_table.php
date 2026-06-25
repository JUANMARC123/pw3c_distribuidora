<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('gastos_operativos', function (Blueprint $table) {
            $table->bigIncrements('id_gasto');
            $table->unsignedBigInteger('id_caja');
            $table->string('tipo_gasto', 50);
            $table->string('descripcion', 255)->nullable();
            $table->decimal('monto', 10, 2);
            $table->string('comprobante', 255)->nullable();
            $table->dateTime('fecha')->useCurrent();

            $table->foreign('id_caja')
                  ->references('id_caja')
                  ->on('caja_repartidor')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('gastos_operativos');
    }
};
