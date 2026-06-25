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
            $table->unsignedBigInteger('id_control_ruta')->nullable()->unique();
            $table->unsignedBigInteger('id_repartidor');
            $table->date('fecha');
            $table->decimal('monto_inicial', 10, 2)->default(0);
            $table->decimal('ingresos', 10, 2)->default(0);
            $table->decimal('gastos', 10, 2)->default(0);
            $table->decimal('monto_final', 10, 2)->default(0);
            $table->unsignedTinyInteger('id_estado_caja')->default(1);

            $table->foreign('id_control_ruta')
                  ->references('id_control_ruta')
                  ->on('control_rutas')
                  ->onDelete('set null');

            $table->foreign('id_repartidor')
                  ->references('id_repartidor')
                  ->on('repartidores')
                  ->onDelete('restrict');

            $table->foreign('id_estado_caja')
                  ->references('id_estado_caja')
                  ->on('estados_caja')
                  ->onDelete('restrict');

            $table->unique(['id_repartidor', 'fecha']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('caja_repartidor');
    }
};
