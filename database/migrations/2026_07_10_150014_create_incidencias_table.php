<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('incidencias', function (Blueprint $table) {
            $table->bigIncrements('id_incidencia');
            $table->unsignedBigInteger('id_despacho');
            $table->enum('tipo_incidencia', ['cliente_ausente', 'producto_danado', 'retraso_trafico', 'devolucion_parcial', 'otro']);
            $table->text('descripcion')->nullable();
            $table->string('archivo')->nullable();
            $table->timestamp('fecha_incidencia')->useCurrent();
            $table->timestamps();

            $table->foreign('id_despacho')
                ->references('id_despacho')->on('despachos')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('incidencias');
    }
};
