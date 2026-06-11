<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id('id_vehiculo');
            $table->string('placa', 20)->unique();
            $table->unsignedSmallInteger('id_modelo');
            $table->tinyInteger('id_capacidad')->unsigned();
            $table->tinyInteger('id_estado_vehiculo')->unsigned();

            $table->foreign('id_modelo')
                  ->references('id_modelo')
                  ->on('modelos')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_capacidad')
                  ->references('id_capacidad')
                  ->on('capacidades')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreign('id_estado_vehiculo')
                  ->references('id_estado_vehiculo')
                  ->on('estados_vehiculo')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehiculos');
    }
};
