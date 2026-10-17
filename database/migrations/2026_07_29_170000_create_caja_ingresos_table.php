<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('caja_ingresos', function (Blueprint $table) {
            $table->bigIncrements('id_ingreso');
            $table->unsignedBigInteger('id_caja');
            $table->unsignedBigInteger('id_hoja_ruta')->nullable();
            $table->string('descripcion')->nullable();
            $table->decimal('monto', 10, 2);
            $table->timestamps();

            $table->foreign('id_caja')
                ->references('id_caja')->on('caja_repartidor')
                ->onDelete('cascade');
            $table->foreign('id_hoja_ruta')
                ->references('id_hoja_ruta')->on('hojas_ruta')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('caja_ingresos');
    }
};
