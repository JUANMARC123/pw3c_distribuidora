<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('historial_estado_repartidor', function (Blueprint $table) {
            $table->id('id_historial');
            $table->unsignedBigInteger('id_repartidor');
            $table->tinyInteger('id_estado_repartidor')->unsigned();
            $table->timestamp('fecha_inicio')->useCurrent();
            $table->timestamp('fecha_fin')->nullable();

            $table->foreign('id_repartidor')
                  ->references('id_repartidor')
                  ->on('repartidores')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('id_estado_repartidor')
                  ->references('id_estado_repartidor')
                  ->on('estados_repartidor')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->unique(['id_repartidor', 'fecha_inicio']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('historial_estado_repartidor');
    }
};
