<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('evidencias_entrega', function (Blueprint $table) {
            $table->bigIncrements('id_evidencia');
            $table->unsignedBigInteger('id_despacho');
            $table->enum('tipo_evidencia', ['firma', 'foto', 'sello']);
            $table->string('archivo');
            $table->timestamps();

            $table->foreign('id_despacho')
                ->references('id_despacho')->on('despachos')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('evidencias_entrega');
    }
};
